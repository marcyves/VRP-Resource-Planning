@props(['documents'])
<!-- Start advanced-course-table.blade  -->
<section class="bg-gray-50 p-3 sm:p-5">
    <div class="mx-auto max-w-screen-xl px-2 lg:px-12">
        <div class="bg-white relative shadow-md sm:rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 text-center">
                        <tr>
                            <th scope="col" class="px-2 py-3 mr-8 w-10">Type</th>
                            <th scope="col" class="px-2 py-3 text-center">Description</th>
                            <th scope="col" class="px-2 py-3 text-center">Year</th>
                            <th scope="col" class="px-2 py-3 text-center">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($documents as $document)
                        <tr>
                            <td class="px-2 py-3 font-medium text-gray-900 whitespace-nowrap">
                                <img src="{{asset('/icons/'.substr($document->file_name, -3).'.png')}}" alt="{{substr($document->file_name, -3)}}">
                            </td>
                            <th scope="row" class="px-2 py-3 font-medium text-gray-900 whitespace-nowrap">
                                    <a target="_blank" 
                                        mime="application/pdf"
                                        class="inline-flex items-center p-0.5 text-sm font-medium text-center text-blue-500 hover:text-gray-800 rounded-lg focus:outline-none"
                                        href="{{route('documents.show', $document->id)}}"
                                        >
                                    {{$document->description}}
                                    </a>    
                            </th>
                            <td class="px-2 py-3 text-center">{{$document->year}}</td>
                            <td class="px-2 py-3 flex items-center justify-end">
                                <form action="{{route('documents.edit', $document->id)}}" method="get">
                                    <button class="inline-flex items-center p-0.5 text-sm font-medium text-center text-green-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                                            </svg>                                  
                                    </button>    
                                </form>
                                <a class="mb-1 p-0.5 text-sm font-medium text-center text-red-500 hover:text-gray-800 rounded-lg focus:outline-none" 
                                   data-toggle="modal" id="smallButton" data-target="#smallModal" data-attr="{{ route('documents.delete', $document->id) }}" title="Delete Document">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </a>
                
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </section>
    <!-- End advanced-course-table.blade  -->