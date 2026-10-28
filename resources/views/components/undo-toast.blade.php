{{--
    "X archived. Undo" toast. Archive actions flash session('undo') =
    ['message' => ..., 'url' => <unarchive route>]; Undo PATCHes that route
    with undo=1 so it sends the user back to the page they were on.
--}}
@if($undoDone = session('undo_done'))
<div class="undo-toast" id="undoToast" role="status" aria-live="polite">
    <i class="fas fa-rotate-left undo-toast-icon"></i>
    <span class="undo-toast-message">{{ $undoDone }}</span>
    <button type="button" class="undo-toast-close" aria-label="Dismiss" onclick="document.getElementById('undoToast').remove()">
        <i class="fas fa-times"></i>
    </button>
</div>
@elseif($undo = session('undo'))
<div class="undo-toast" id="undoToast" role="status" aria-live="polite">
    <i class="fas fa-check undo-toast-icon"></i>
    <span class="undo-toast-message">{{ $undo['message'] }}</span>
    <form method="POST" action="{{ $undo['url'] }}" class="m-0">
        @csrf
        @method('PATCH')
        <input type="hidden" name="undo" value="1">
        <button type="submit" class="undo-toast-btn">Undo</button>
    </form>
    <button type="button" class="undo-toast-close" aria-label="Dismiss" onclick="document.getElementById('undoToast').remove()">
        <i class="fas fa-times"></i>
    </button>
</div>
@endif
@if(session('undo') || session('undo_done'))
<script>
    setTimeout(function () {
        var toast = document.getElementById('undoToast');
        if (toast && !toast.matches(':hover') && !toast.contains(document.activeElement)) {
            toast.classList.add('is-leaving');
            setTimeout(function () { toast.remove(); }, 250);
        }
    }, 8000);
</script>
@endif
