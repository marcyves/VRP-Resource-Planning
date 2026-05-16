<form action="{{ route('documents.destroy', $document->id) }}" method="post">
    <div class="modal-body">
        @csrf
        @method('DELETE')
        <h5>{{ __('messages.delete_document_confirm', ['description' => $document->description]) }}</h5>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('messages.cancel') }}</button>
        <button type="submit" class="btn btn-danger">{{ __('messages.delete') }}</button>
    </div>
</form>
