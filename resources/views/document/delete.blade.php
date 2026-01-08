<form action="{{ route('documents.destroy', $document->id) }}" method="post">
    <div class="modal-body relative border-box p-8 bg-blue-200 rounded-lg">
        @csrf
        @method('DELETE')
        <h5 class="text-center">Are you sure you want to delete {{ $document->description }} ?</h5>
    </div>
    <div class="modal-footer">
        <button type="button"
            class="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-gray-800 uppercase tracking-widest hover:bg-gray-400 focus:bg-gray-400 active:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150"
            data-dismiss="modal">Cancel</button>
        <button type="submit"
            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">Delete</button>
    </div>
</form>