<form action="{{ route('documents.destroy', $document->id) }}" method="post">
    <div class="modal-body">
        @csrf
        @method('DELETE')
        <h5>Are you sure you want to delete {{ $document->description }}?</h5>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-danger">Delete</button>
    </div>
</form>
