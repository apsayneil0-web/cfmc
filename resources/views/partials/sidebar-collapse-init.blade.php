<script>
    // Applies the saved sidebar-collapsed state before the page paints, the
    // same way the theme script above avoids a flash — otherwise the
    // sidebar renders expanded first and only collapses once the toggle
    // script near the end of <body> runs, which looks like a glitch on
    // every navigation.
    (function () {
        try {
            if (window.innerWidth >= 992 && localStorage.getItem('cfmc-sidebar-collapsed') === '1') {
                document.documentElement.classList.add('sidebar-collapsed');
            }
        } catch (e) {}
    })();
</script>
