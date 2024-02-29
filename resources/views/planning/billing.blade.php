<x-app-layout>
    <x-slot name="header" class="print:hidden">
        <h2 class="print:hidden font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Billing Preparation') }} {{$current_month}}/{{$current_year}}
        </h2>
    </x-slot>

    @if($monthly_hours == 0)
    <x-nice-box color="white">
    <div class="flex flex-row font-semibold text-gray-600 border border-gray-300 rounded-md mt-4 p-4 bg-red-100">
        No hours logged this month
    </div>
    </x-nice-box>
    @else
        @foreach($schools as $school => $courses)
        <x-nice-box color="white">
            <div class="font-bold text-gray-800 bg-green-100 p-2 mb-2 flex justify-between">
                <h2 class="inline ml-2 pt-2">{{$school}}</h2>
            </div>
            
            <x-course-planning :courses=$courses :bills=$bills current_month={{$current_month}} current_year={{$current_year}}/>

        </x-nice-box>
        @endforeach
        <x-nice-box color="white">
        <div class="flex flex-row justify-between font-semibold text-gray-600 border border-gray-300 rounded-md mt-4 py-4 bg-gray-200">
            <div class="mx-4">
                Time worked = {{$monthly_hours}} hours
            </div>
            <div class="mx-4">
                Monthly gain = {{number_format($monthly_gain,2)}} €
            </div>
            <div class="mx-4">
                Average Rate = {{number_format($monthly_gain/$monthly_hours,2)}} €
            </div>
        </div>
        </x-nice-box>

    @endif
</x-app-layout>