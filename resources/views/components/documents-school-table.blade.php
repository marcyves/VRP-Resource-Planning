@props(['documents'])
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
                                @if(Auth::user()->getMode() == "Edit")
                                <form action="{{route('documents.edit', $document->id)}}" method="get">
                            <x-button-edit/>
                                </form>
                                <form action="{{route('documents.destroy', $document->id)}}" method="get">
                            <x-button-delete/>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </section>