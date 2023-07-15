@props(['school', 'school_id'])
<section class="bg-gray-50 p-3 sm:p-5">
    <div class="mx-auto max-w-screen-xl px-4 lg:px-12">
        <!-- Start coding here -->
        <div class="bg-white relative shadow-md sm:rounded-lg overflow-hidden">
            <div class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
                <div class="w-full md:w-1/2">
                    {{$school}}
                </div>
                <div class="w-full md:w-auto flex flex-col md:flex-row space-y-2 md:space-y-0 items-stretch md:items-center justify-end md:space-x-3 flex-shrink-0">
                    <button type="button" class="flex items-center justify-center text-white bg-primary-700 hover:bg-primary-800 focus:ring-4 focus:ring-primary-300 font-medium rounded-lg text-sm px-4 py-2 focus:outline-none">
                        <svg class="h-3.5 w-3.5 mr-2" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                            <path clip-rule="evenodd" fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
                        </svg>
                        Add course
                    </button>
                    <div class="flex items-center space-x-3 w-full md:w-auto">
                        <form action="{{route('school.edit', $school_id)}}" method="get">
                            <button class="inline-flex items-center p-0.5 text-sm font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                                Edit
                            </button>    
                        </form>
                        <form action="{{route('school.destroy', $school_id)}}" method="post">
                            @csrf
                            @method('delete')
                            <button class="inline-flex items-center p-0.5 text-sm font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                                Delete
                            </button>    
                        </form>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-3">Course name</th>
                            <th scope="col" class="px-4 py-3">Year</th>
                            <th scope="col" class="px-4 py-3">Semester</th>
                            <th scope="col" class="px-4 py-3">Sessions</th>
                            <th scope="col" class="px-4 py-3">Session length</th>
                            <th scope="col" class="px-4 py-3">Total time</th>
                            <th scope="col" class="px-4 py-3">Rate</th>
                            <th scope="col" class="px-4 py-3">Gain</th>
                            <th scope="col" class="px-4 py-3">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {{$slot}}
                    </tbody>
                </table>
                <a
                class="m-4 inline-flex items-center p-2 text-sm border border-gray-300 rounded-md font-semibold font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none" 
                href="{{route('course.create', $school_id)}}">New Course</a>
            </div>
        </div>
    </div>
    </section>