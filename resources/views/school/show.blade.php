<x-app-layout>
    <x-slot name="header">
        <div class="flex">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight grow py-2">
                {{$school_name}}
            </h2>
            @if(Auth::user()->getMode() == "Edit")
                <div class="flex flex-col md:flex-row space-y-2 md:space-y-0 items-stretch md:items-center justify-end md:space-x-3 flex-shrink-0">
                    <div class="flex items-center space-x-3 w-full md:w-auto">
                        <form action="{{route('school.edit', $school_id)}}" method="get">
<x-button-edit/>
                        </form>
                        <form action="{{route('school.destroy', $school_id)}}" method="post">
                            @csrf
                            @method('delete')
<x-button-delete/>
                        </form>
                        <a
                        class="inline-flex items-center p-2 text-sm border border-gray-300 rounded-md font-semibold font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none" 
                        href="{{route('course.create', $school_id)}}">{{__('messages.add_course')}}</a>
                    </div>
                </div>
            @endif
        </div>
    </x-slot>

    <section class="nice-page">
        <x-advanced-course-table :courses=$courses :school_name=$school_name :school_id=$school_id/>   
        <x-documents-school-table :documents=$documents :school_id=$school_id/>

    <div class="mx-auto max-w-screen-xl px-2 lg:px-12">
        <div class="bg-white relative shadow-md sm:rounded-lg overflow-hidden">
            <div class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
                <div class="w-full text-center text-gray-600">
                    <form action="{{route('document.store', $school_id)}}" 
                        class="flex justify-between" 
                        method="post"
                        enctype="multipart/form-data">
                    @csrf
                    <div class="m-2 mx-6">
                        <input type="text" name="description" id="desc" 
                               class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm mb-6"
                               placeholder="Document Description">
                        @error('description')
                            <div class="text-red-500" role="alert">
                                {{ $message }}
                            </div>
                        @enderror
                        <input type="text" name="year" id="desc" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" value="{{date('Y')}}">
                        @error('year')
                            <div class="text-red-500" role="alert">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="my-4">
                        <input type="file" class="form-control" name="document" @error('document') is-invalid @enderror>
                        @error('document')
                            <div class="text-red-500" role="alert">
                                {{ $message }}
                            </div>
                        @enderror
                        <button class="items-center p-0.5 text-sm font-medium  text-blue-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                            Upload                               
                        </button>
                    </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
    </section>

<!-- small modal -->
<div class="modal fade" id="smallModal" tabindex="-1" role="dialog" aria-labelledby="smallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="smallBody">
                <div>
                    <!-- the result to be displayed apply here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // display a modal (small modal)
    $(document).on('click', '#smallButton', function(event) {
        event.preventDefault();
        let href = $(this).attr('data-attr');
        $.ajax({
            url: href
            , beforeSend: function() {
                $('#loader').show();
            },
            // return the result
            success: function(result) {
                $('#smallModal').modal("show");
                $('#smallBody').html(result).show();
            }
            , complete: function() {
                $('#loader').hide();
            }
            , error: function(jqXHR, testStatus, error) {
                console.log(error);
                alert("Page " + href + " cannot open. Error:" + error);
                $('#loader').hide();
            }
            , timeout: 8000
        })
    });
</script>

</x-app-layout>