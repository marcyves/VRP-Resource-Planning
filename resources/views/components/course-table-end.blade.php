@props(['total_budget', 'total_time'])
</tbody>
<tfoot>
    <tr>
        <th scope="row">{{ __('messages.total') }}</th>
        <td></td>
        <td></td>
        <td>{{ __('messages.session_length') }}</td>
        <td>{{$total_time}}</td>
        <td></td>
        <td></td>
        <td></td>
        <td>{{ __('messages.gain') }}</td>
        <td></td>
        <td>@money($total_budget)</td>
        <td class="flex items-center justify-end"></td>
    </tr>
</tfoot>