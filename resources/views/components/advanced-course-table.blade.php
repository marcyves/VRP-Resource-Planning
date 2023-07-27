@props(['school', 'school_id'])
<section class="bg-gray-50 p-3 sm:p-5">
    <div class="mx-auto max-w-screen-xl px-2 lg:px-12">
        <!-- Start coding here -->
        <div class="bg-white relative shadow-md sm:rounded-lg overflow-hidden">
            <div class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0 md:space-x-4 p-4">
                <div class="w-full md:w-1/2">
                    {{$school}}
                </div>
                <div class="w-full md:w-auto flex flex-col md:flex-row space-y-2 md:space-y-0 items-stretch md:items-center justify-end md:space-x-3 flex-shrink-0">
                    <div class="flex items-center space-x-3 w-full md:w-auto">
                        <form action="{{route('school.edit', $school_id)}}" method="get">
                            <button class="inline-flex items-center p-0.5 text-sm font-medium text-center text-green-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                                Edit
                            </button>    
                        </form>
                        <form action="{{route('school.destroy', $school_id)}}" method="post">
                            @csrf
                            @method('delete')
                            <button class="inline-flex items-center p-0.5 text-sm font-medium text-center text-red-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                                Delete
                            </button>    
                        </form>
                        <form action="{{route('school.show', $school_id)}}" method="get">
                            @csrf
                            <button class="inline-flex items-center p-0.5 text-sm font-medium text-center text-blue-500 hover:text-gray-800 rounded-lg focus:outline-none" type="submit">
                                View
                            </button>    
                        </form>
                        <a
                        class="inline-flex items-center p-2 text-sm border border-gray-300 rounded-md font-semibold font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none" 
                        href="{{route('course.create', $school_id)}}">Add Course</a>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 text-center">
                        <tr>
                            <th scope="col" class="px-2 py-3">Course name</th>
                            <th scope="col" class="px-2 py-3 text-center">Year</th>
                            <th scope="col" class="px-2 py-3 text-center">Semester</th>
                            <th scope="col" class="px-2 py-3 text-center">Sessions</th>
                            <th scope="col" class="px-2 py-3 text-center">Session length</th>
                            <th scope="col" class="px-2 py-3 text-center">Time</th>
                            <th scope="col" class="px-2 py-3 text-center">Groups</th>
                            <th scope="col" class="px-2 py-3 text-center">Total time</th>
                            <th scope="col" class="px-2 py-3 text-center">Rate</th>
                            <th scope="col" class="px-2 py-3 text-center">Gain</th>
                            <th scope="col" class="px-2 py-3 text-center">
                                <span class="sr-only">Actions</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        {{$slot}}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </section>