<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{$school_name}} Details
        </h2>
    </x-slot>

    <section class="bg-gray-50 p-3 sm:p-5">
    <div class="mx-auto max-w-screen-xl px-2 lg:px-12">
        <div class="bg-white relative shadow-md sm:rounded-lg overflow-hidden">              
        <x-school-header :school_name=$school_name :school_id=$school_id/>
        <x-advanced-course-table :courses=$courses :school_name=$school_name :school_id=$school_id/>
        </div>
    </div>
    </section>
    
    <x-documents-school-table :documents=$documents :school_id=$school_id/>

    <section class="bg-gray-50 p-3 sm:p-5">
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
</x-app-layout>