{{--
    In-page viewer for uploaded photos and PDFs.

    Include once per layout: <x-file-viewer />
    Then mark any link to a file:
        <a href="..." target="_blank" data-file-viewer
           data-viewer-title="Valid ID" data-viewer-group="farmer-12">View</a>

    A plain click opens the viewer; Ctrl/Cmd/middle-click still opens a new
    tab, and without JS the link behaves exactly as before. Links that share
    a data-viewer-group can be stepped through with the arrows.
    Optional: data-viewer-type="image" | "pdf" (or a MIME type) when the URL
    has no file extension, e.g. a blob: URL for a just-picked file.
--}}
@once
<div class="fv" id="fileViewer" hidden role="dialog" aria-modal="true" aria-labelledby="fvTitle">
    <div class="fv-backdrop" data-fv-close></div>
    <div class="fv-panel">
        <div class="fv-bar">
            <div class="fv-heading">
                <span class="fv-title" id="fvTitle">Document</span>
                <span class="fv-count" id="fvCount"></span>
            </div>
            <div class="fv-actions">
                <div class="fv-zoom" id="fvZoom">
                    <button type="button" class="fv-btn" data-fv-zoom="-1" aria-label="Zoom out" title="Zoom out"><i class="fas fa-minus"></i></button>
                    <button type="button" class="fv-btn fv-zoom-level" data-fv-zoom="0" aria-label="Reset zoom" title="Reset zoom" id="fvZoomLevel">100%</button>
                    <button type="button" class="fv-btn" data-fv-zoom="1" aria-label="Zoom in" title="Zoom in"><i class="fas fa-plus"></i></button>
                </div>
                <a class="fv-btn" id="fvDownload" href="#" download aria-label="Download" title="Download"><i class="fas fa-download"></i></a>
                <a class="fv-btn" id="fvOpen" href="#" target="_blank" rel="noopener" aria-label="Open in new tab" title="Open in new tab"><i class="fas fa-up-right-from-square"></i></a>
                <button type="button" class="fv-btn fv-close" data-fv-close aria-label="Close" title="Close (Esc)"><i class="fas fa-xmark"></i></button>
            </div>
        </div>
        <div class="fv-stage" id="fvStage">
            <button type="button" class="fv-nav fv-prev" id="fvPrev" aria-label="Previous document"><i class="fas fa-chevron-left"></i></button>
            <div class="fv-content" id="fvContent"></div>
            <button type="button" class="fv-nav fv-next" id="fvNext" aria-label="Next document"><i class="fas fa-chevron-right"></i></button>
            <div class="fv-loading" id="fvLoading" aria-hidden="true"><span></span></div>
        </div>
    </div>
</div>

<style>
    .fv { position: fixed; inset: 0; z-index: 2000; display: flex; align-items: center; justify-content: center; padding: clamp(0.5rem, 3vw, 2rem); }
    .fv[hidden] { display: none; }
    .fv-backdrop { position: absolute; inset: 0; background: rgba(16, 22, 18, 0.72); backdrop-filter: blur(4px); animation: fv-fade 200ms ease-out; }
    .fv-panel {
        position: relative; display: flex; flex-direction: column; width: min(1100px, 100%); height: min(860px, 100%);
        background: var(--brand-surface, #fff); color: var(--text-primary, #1f2a22);
        border: 1px solid var(--brand-border, #e4e2da); border-radius: 1rem; overflow: hidden;
        box-shadow: 0 30px 60px -20px rgba(0, 0, 0, 0.5); animation: fv-in 260ms cubic-bezier(0.16, 1, 0.3, 1);
    }
    .fv-bar { display: flex; align-items: center; justify-content: space-between; gap: 1rem; padding: 0.65rem 0.75rem 0.65rem 1.1rem; border-bottom: 1px solid var(--brand-border, #e4e2da); }
    .fv-heading { min-width: 0; display: flex; align-items: baseline; gap: 0.6rem; }
    .fv-title { font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .fv-count { font-size: 0.8rem; color: var(--text-muted, #6b7069); white-space: nowrap; font-variant-numeric: tabular-nums; }
    .fv-actions { display: flex; align-items: center; gap: 0.25rem; flex-shrink: 0; }
    .fv-zoom { display: flex; align-items: center; gap: 0.15rem; margin-right: 0.35rem; padding-right: 0.5rem; border-right: 1px solid var(--brand-border, #e4e2da); }
    .fv-zoom[hidden] { display: none; }
    .fv-btn {
        display: inline-grid; place-items: center; min-width: 2.25rem; height: 2.25rem; padding: 0 0.4rem; border: 0; border-radius: 0.5rem;
        background: transparent; color: var(--text-secondary, #4a5249); text-decoration: none; cursor: pointer;
        transition: background-color 150ms ease, color 150ms ease;
    }
    .fv-btn:hover { background: var(--brand-primary-light, #e6eee7); color: var(--brand-primary, #2f5d3a); }
    .fv-btn:focus-visible { outline: 2px solid var(--brand-primary, #2f5d3a); outline-offset: 1px; }
    .fv-zoom-level { font-size: 0.8rem; font-variant-numeric: tabular-nums; min-width: 3.25rem; }
    .fv-close:hover { background: var(--brand-danger-light, #f6e5e0); color: var(--brand-danger-text, #9c4532); }

    .fv-stage { position: relative; flex: 1; min-height: 0; background: var(--brand-surface-muted, #f9f8f5); }
    .fv-content { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; overflow: hidden; }
    .fv-content img { max-width: calc(100% - 2rem); max-height: calc(100% - 2rem); object-fit: contain; border-radius: 0.35rem; box-shadow: 0 10px 30px -14px rgba(0, 0, 0, 0.35); transform-origin: center center; transition: transform 200ms ease; user-select: none; -webkit-user-drag: none; }
    .fv-content.is-zoomed img { cursor: grab; transition: none; }
    .fv-content.is-dragging img { cursor: grabbing; }
    .fv-content iframe { width: 100%; height: 100%; border: 0; background: #fff; }
    .fv-message { text-align: center; max-width: 26rem; padding: 2rem; color: var(--text-secondary, #4a5249); }
    .fv-message i { font-size: 2rem; color: var(--text-muted, #6b7069); margin-bottom: 0.75rem; display: block; }
    .fv-message p { margin: 0 0 1rem; }
    .fv-pdf-hint { position: absolute; left: 50%; bottom: 0.75rem; transform: translateX(-50%); font-size: 0.78rem; background: var(--brand-surface, #fff); border: 1px solid var(--brand-border, #e4e2da); border-radius: 999px; padding: 0.35rem 0.85rem; color: var(--text-secondary, #4a5249); white-space: nowrap; }
    .fv-pdf-hint a { color: var(--brand-primary, #2f5d3a); font-weight: 600; }

    .fv-nav {
        position: absolute; z-index: 2; top: 50%; transform: translateY(-50%); width: 2.75rem; height: 2.75rem; border-radius: 999px; border: 1px solid var(--brand-border, #e4e2da);
        background: var(--brand-surface, #fff); color: var(--text-primary, #1f2a22); display: grid; place-items: center; cursor: pointer;
        box-shadow: 0 6px 16px -8px rgba(0, 0, 0, 0.35); transition: background-color 150ms ease, color 150ms ease;
    }
    .fv-nav:hover { background: var(--brand-primary, #2f5d3a); color: var(--brand-on-primary, #fff); border-color: var(--brand-primary, #2f5d3a); }
    .fv-nav[hidden] { display: none; }
    .fv-prev { left: 0.85rem; }
    .fv-next { right: 0.85rem; }

    .fv-loading { position: absolute; inset: 0; display: grid; place-items: center; pointer-events: none; }
    .fv-loading[hidden] { display: none; }
    .fv-loading span { width: 2rem; height: 2rem; border-radius: 50%; border: 3px solid var(--brand-border, #e4e2da); border-top-color: var(--brand-primary, #2f5d3a); animation: fv-spin 700ms linear infinite; }

    @keyframes fv-fade { from { opacity: 0; } }
    @keyframes fv-in { from { opacity: 0; transform: translateY(12px) scale(0.98); } }
    @keyframes fv-spin { to { transform: rotate(360deg); } }
    @media (prefers-reduced-motion: reduce) { .fv-backdrop, .fv-panel { animation: none; } .fv-content img { transition: none; } }
    @media (max-width: 575.98px) {
        .fv { padding: 0; }
        .fv-panel { border-radius: 0; border: 0; height: 100%; }
        .fv-zoom { display: none; }
        .fv-nav { width: 2.4rem; height: 2.4rem; }
    }
</style>

<script>
    (function () {
        var viewer = document.getElementById('fileViewer');
        if (!viewer) return;
        var content = document.getElementById('fvContent');
        var titleEl = document.getElementById('fvTitle');
        var countEl = document.getElementById('fvCount');
        var loading = document.getElementById('fvLoading');
        var zoomBar = document.getElementById('fvZoom');
        var zoomLabel = document.getElementById('fvZoomLevel');
        var dl = document.getElementById('fvDownload');
        var openNew = document.getElementById('fvOpen');
        var prevBtn = document.getElementById('fvPrev');
        var nextBtn = document.getElementById('fvNext');
        var homeParent = viewer.parentNode;

        var items = [], index = 0, lastFocus = null, lockedBody = false;
        var scale = 1, panX = 0, panY = 0, img = null;

        function typeOf(link) {
            var t = (link.getAttribute('data-viewer-type') || '').toLowerCase();
            if (t.indexOf('image') === 0) return 'image';
            if (t === 'pdf' || t === 'application/pdf') return 'pdf';
            var path = (link.getAttribute('href') || '').split(/[?#]/)[0].toLowerCase();
            if (/\.(jpe?g|png|gif|webp|bmp|svg)$/.test(path)) return 'image';
            if (/\.pdf$/.test(path)) return 'pdf';
            if (path.indexOf('blob:') === 0 || path.indexOf('data:image') === 0) return 'guess';
            return 'other';
        }

        function titleOf(link) {
            return link.getAttribute('data-viewer-title') || link.textContent.replace(/\s+/g, ' ').trim() || 'Document';
        }

        function applyTransform() {
            if (!img) return;
            img.style.transform = 'translate(' + panX + 'px,' + panY + 'px) scale(' + scale + ')';
            content.classList.toggle('is-zoomed', scale > 1);
            zoomLabel.textContent = Math.round(scale * 100) + '%';
        }
        function setZoom(next) {
            scale = Math.min(4, Math.max(1, next));
            if (scale === 1) { panX = 0; panY = 0; }
            applyTransform();
        }

        function showMessage(icon, text, url) {
            content.innerHTML = '';
            var box = document.createElement('div'); box.className = 'fv-message';
            box.innerHTML = '<i class="fas ' + icon + '"></i><p></p>';
            box.querySelector('p').textContent = text;
            if (url) {
                var a = document.createElement('a');
                a.href = url; a.target = '_blank'; a.rel = 'noopener';
                a.className = 'btn btn-outline-primary btn-sm';
                a.innerHTML = '<i class="fas fa-up-right-from-square me-1"></i> Open in new tab';
                box.appendChild(a);
            }
            content.appendChild(box);
        }

        function renderImage(url, onFail) {
            img = new Image();
            img.alt = titleEl.textContent;
            img.draggable = false;
            img.onload = function () { loading.hidden = true; };
            img.onerror = function () { img = null; if (onFail) { onFail(); } else { loading.hidden = true; showMessage('fa-image', 'This photo could not be loaded.', url); } };
            img.src = url;
            content.appendChild(img);
            zoomBar.hidden = false;
            setZoom(1);
        }

        function renderPdf(url) {
            zoomBar.hidden = true;
            var frame = document.createElement('iframe');
            frame.title = titleEl.textContent;
            frame.src = url;
            frame.onload = function () { loading.hidden = true; };
            content.appendChild(frame);
            // Phones often can't show PDFs inside a page; offer the way out.
            if (window.matchMedia('(pointer: coarse)').matches) {
                var hint = document.createElement('div'); hint.className = 'fv-pdf-hint';
                hint.innerHTML = 'PDF not showing? <a target="_blank" rel="noopener">Open it here</a>';
                hint.querySelector('a').href = url;
                content.appendChild(hint);
            }
            setTimeout(function () { loading.hidden = true; }, 4000);
        }

        function show(i) {
            index = (i + items.length) % items.length;
            var link = items[index];
            var url = link.href;
            content.innerHTML = '';
            img = null;
            loading.hidden = false;
            titleEl.textContent = titleOf(link);
            countEl.textContent = items.length > 1 ? (index + 1) + ' of ' + items.length : '';
            prevBtn.hidden = nextBtn.hidden = items.length < 2;
            dl.href = url; openNew.href = url;
            var name = (url.split(/[?#]/)[0].split('/').pop() || 'document');
            dl.setAttribute('download', url.indexOf('blob:') === 0 ? titleOf(link) : decodeURIComponent(name));

            var type = typeOf(link);
            if (type === 'image') renderImage(url);
            else if (type === 'pdf') renderPdf(url);
            else if (type === 'guess') renderImage(url, function () { renderPdf(url); });
            else { zoomBar.hidden = true; loading.hidden = true; showMessage('fa-file', "A preview isn't available for this file type. You can download it or open it in a new tab.", url); }
        }

        function open(link) {
            var group = link.getAttribute('data-viewer-group');
            items = group
                ? Array.prototype.filter.call(document.querySelectorAll('a[data-file-viewer][data-viewer-group="' + group.replace(/"/g, '\\"') + '"]'), function (a) { return a.getAttribute('href') && !a.classList.contains('d-none'); })
                : [link];
            // Only step through documents in the same dialog as the clicked link
            // (the same record's files can appear in several modals on one page).
            var scope = link.closest('.modal');
            items = items.filter(function (a) { return a.closest('.modal') === scope; });
            if (items.indexOf(link) < 0) items = [link];

            // Inside a Bootstrap modal, live inside it so its focus trap and Esc handling stay happy.
            var host = link.closest('.modal') || homeParent;
            if (viewer.parentNode !== host) host.appendChild(viewer);
            if (host === homeParent && document.body.style.overflow !== 'hidden') { document.body.style.overflow = 'hidden'; lockedBody = true; }

            lastFocus = document.activeElement;
            viewer.hidden = false;
            show(items.indexOf(link));
            viewer.querySelector('.fv-close').focus();
        }

        function close() {
            if (viewer.hidden) return;
            viewer.hidden = true;
            content.innerHTML = '';
            img = null;
            if (lockedBody) { document.body.style.overflow = ''; lockedBody = false; }
            if (viewer.parentNode !== homeParent) homeParent.appendChild(viewer);
            if (lastFocus && document.contains(lastFocus)) lastFocus.focus();
        }

        // Open on a plain click; let Ctrl/Cmd/Shift/middle-click keep their normal new-tab behaviour.
        document.addEventListener('click', function (e) {
            var link = e.target.closest && e.target.closest('a[data-file-viewer]');
            if (!link || !link.getAttribute('href') || e.defaultPrevented) return;
            if (e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
            e.preventDefault();
            open(link);
        });

        viewer.addEventListener('click', function (e) {
            if (e.target.closest('[data-fv-close]')) { close(); return; }
            var z = e.target.closest('[data-fv-zoom]');
            if (z) { var d = +z.getAttribute('data-fv-zoom'); setZoom(d === 0 ? 1 : scale + d * 0.5); }
        });
        prevBtn.addEventListener('click', function () { show(index - 1); });
        nextBtn.addEventListener('click', function () { show(index + 1); });

        // Keyboard: capture phase so Esc closes only the viewer, not the modal underneath.
        window.addEventListener('keydown', function (e) {
            if (viewer.hidden) return;
            if (e.key === 'Escape') { e.preventDefault(); e.stopImmediatePropagation(); close(); }
            else if (e.key === 'ArrowLeft' && items.length > 1) { e.preventDefault(); show(index - 1); }
            else if (e.key === 'ArrowRight' && items.length > 1) { e.preventDefault(); show(index + 1); }
            else if ((e.key === '+' || e.key === '=') && img) { setZoom(scale + 0.5); }
            else if (e.key === '-' && img) { setZoom(scale - 0.5); }
            else if (e.key === 'Tab') {
                var f = Array.prototype.filter.call(viewer.querySelectorAll('button, a[href], iframe'), function (el) { return el.offsetParent !== null; });
                if (!f.length) return;
                if (e.shiftKey && document.activeElement === f[0]) { e.preventDefault(); f[f.length - 1].focus(); }
                else if (!e.shiftKey && document.activeElement === f[f.length - 1]) { e.preventDefault(); f[0].focus(); }
            }
        }, true);

        // Photos: double-click to zoom, wheel to zoom, drag to move around when zoomed.
        content.addEventListener('dblclick', function (e) { if (img && e.target === img) setZoom(scale > 1 ? 1 : 2); });
        content.addEventListener('wheel', function (e) {
            if (!img) return;
            e.preventDefault();
            setZoom(scale + (e.deltaY < 0 ? 0.25 : -0.25));
        }, { passive: false });
        var dragging = false, sx = 0, sy = 0, ox = 0, oy = 0;
        content.addEventListener('pointerdown', function (e) {
            if (!img || scale === 1 || e.target !== img) return;
            dragging = true; sx = e.clientX; sy = e.clientY; ox = panX; oy = panY;
            content.classList.add('is-dragging'); img.setPointerCapture(e.pointerId);
        });
        content.addEventListener('pointermove', function (e) {
            if (!dragging) return;
            panX = ox + (e.clientX - sx); panY = oy + (e.clientY - sy); applyTransform();
        });
        function endDrag() { dragging = false; content.classList.remove('is-dragging'); }
        content.addEventListener('pointerup', endDrag);
        content.addEventListener('pointercancel', endDrag);
    })();
</script>
@endonce
