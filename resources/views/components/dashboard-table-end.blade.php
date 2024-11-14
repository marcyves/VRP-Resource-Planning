@props(['total_budget'])
    <tr class="footer">
        <th scope="row">{{ __('messages.total') }}</th>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>{{ __('messages.gain') }}</td>
        <td></td>
        <td>@money($total_budget)</td>
        <td class="flex items-center justify-end"></td>
    </tr>
    </tbody>
</table>