<table>
    <thead>
        <tr>
            <th rowspan="3">CATEGORY</th>
            <th rowspan="3">No</th>
            <th rowspan="3">Product Code</th>
            <th rowspan="3">Description</th>
            
            <th colspan="8" align="center">{{ $headers['w4']['label'] }}</th>
            <th colspan="8" align="center">{{ $headers['w3']['label'] }}</th>
            <th colspan="8" align="center">{{ $headers['w2']['label'] }}</th>
            <th colspan="8" align="center">{{ $headers['w1']['label'] }}</th>
            <th colspan="8" align="center">AVG 4 MINGGU</th>
            <th colspan="8" align="center">D-DAY ({{ $headers['d_day']['label'] }})</th>
            <th colspan="10" align="center">SELISIH</th>
        </tr>

        <tr>
            @for ($i = 0; $i < 6; $i++)
                <th colspan="2" align="center">JUMAT</th>
                <th colspan="2" align="center">SABTU</th>
                <th colspan="2" align="center">MINGGU</th>
                <th colspan="2" align="center">SUM</th>
            @endfor

            <th colspan="2" align="center">JUMAT</th>
            <th colspan="2" align="center">SABTU</th>
            <th colspan="2" align="center">MINGGU</th>
            <th colspan="2" align="center">SUM</th>
            <th rowspan="2" align="center">Qty (%)</th>
            <th rowspan="2" align="center">Value (%)</th>
        </tr>

        <tr>
            @for ($i = 0; $i < 28; $i++)
                <th align="center">Qty</th>
                <th align="center">Value</th>
            @endfor
        </tr>
    </thead>
    <tbody>
        @foreach($products as $product)
        <tr>
            <td>{{ $product['category'] }}</td>
            <td>{{ $product['no'] }}</td>
            <td>{{ $product['sku'] }}</td>
            <td>{{ $product['description'] }}</td>
            
            <td>{{ $product['weeks']['w4']['jumat']['qty'] }}</td> <td>{{ $product['weeks']['w4']['jumat']['val'] }}</td>
            <td>{{ $product['weeks']['w4']['sabtu']['qty'] }}</td> <td>{{ $product['weeks']['w4']['sabtu']['val'] }}</td>
            <td>{{ $product['weeks']['w4']['minggu']['qty'] }}</td> <td>{{ $product['weeks']['w4']['minggu']['val'] }}</td>
            <td>{{ $product['weeks']['w4']['sum']['qty'] }}</td> <td>{{ $product['weeks']['w4']['sum']['val'] }}</td>
            
            <td>{{ $product['weeks']['w3']['jumat']['qty'] }}</td> <td>{{ $product['weeks']['w3']['jumat']['val'] }}</td>
            <td>{{ $product['weeks']['w3']['sabtu']['qty'] }}</td> <td>{{ $product['weeks']['w3']['sabtu']['val'] }}</td>
            <td>{{ $product['weeks']['w3']['minggu']['qty'] }}</td> <td>{{ $product['weeks']['w3']['minggu']['val'] }}</td>
            <td>{{ $product['weeks']['w3']['sum']['qty'] }}</td> <td>{{ $product['weeks']['w3']['sum']['val'] }}</td>
            
            <td>{{ $product['weeks']['w2']['jumat']['qty'] }}</td> <td>{{ $product['weeks']['w2']['jumat']['val'] }}</td>
            <td>{{ $product['weeks']['w2']['sabtu']['qty'] }}</td> <td>{{ $product['weeks']['w2']['sabtu']['val'] }}</td>
            <td>{{ $product['weeks']['w2']['minggu']['qty'] }}</td> <td>{{ $product['weeks']['w2']['minggu']['val'] }}</td>
            <td>{{ $product['weeks']['w2']['sum']['qty'] }}</td> <td>{{ $product['weeks']['w2']['sum']['val'] }}</td>
            
            <td>{{ $product['weeks']['w1']['jumat']['qty'] }}</td> <td>{{ $product['weeks']['w1']['jumat']['val'] }}</td>
            <td>{{ $product['weeks']['w1']['sabtu']['qty'] }}</td> <td>{{ $product['weeks']['w1']['sabtu']['val'] }}</td>
            <td>{{ $product['weeks']['w1']['minggu']['qty'] }}</td> <td>{{ $product['weeks']['w1']['minggu']['val'] }}</td>
            <td>{{ $product['weeks']['w1']['sum']['qty'] }}</td> <td>{{ $product['weeks']['w1']['sum']['val'] }}</td>
            
            <td>{{ $product['avg']['jumat']['qty'] }}</td> <td>{{ $product['avg']['jumat']['val'] }}</td>
            <td>{{ $product['avg']['sabtu']['qty'] }}</td> <td>{{ $product['avg']['sabtu']['val'] }}</td>
            <td>{{ $product['avg']['minggu']['qty'] }}</td> <td>{{ $product['avg']['minggu']['val'] }}</td>
            <td>{{ $product['avg']['sum']['qty'] }}</td> <td>{{ $product['avg']['sum']['val'] }}</td>
            
            <td>{{ $product['weeks']['d_day']['jumat']['qty'] }}</td> <td>{{ $product['weeks']['d_day']['jumat']['val'] }}</td>
            <td>{{ $product['weeks']['d_day']['sabtu']['qty'] }}</td> <td>{{ $product['weeks']['d_day']['sabtu']['val'] }}</td>
            <td>{{ $product['weeks']['d_day']['minggu']['qty'] }}</td> <td>{{ $product['weeks']['d_day']['minggu']['val'] }}</td>
            <td>{{ $product['weeks']['d_day']['sum']['qty'] }}</td> <td>{{ $product['weeks']['d_day']['sum']['val'] }}</td>
            
            <td>{{ $product['selisih']['jumat']['qty'] }}</td> <td>{{ $product['selisih']['jumat']['val'] }}</td>
            <td>{{ $product['selisih']['sabtu']['qty'] }}</td> <td>{{ $product['selisih']['sabtu']['val'] }}</td>
            <td>{{ $product['selisih']['minggu']['qty'] }}</td> <td>{{ $product['selisih']['minggu']['val'] }}</td>
            <td>{{ $product['selisih']['sum']['qty'] }}</td> <td>{{ $product['selisih']['sum']['val'] }}</td>
            
            <td>{{ number_format($product['selisih_pct']['qty'], 2) }}</td>
            <td>{{ number_format($product['selisih_pct']['val'], 2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>