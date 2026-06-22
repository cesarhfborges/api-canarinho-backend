@php
    use Knuckles\Scribe\Tools\WritingUtils as u;
    /** @var Knuckles\Camel\Output\OutputEndpointData $endpoint */
@endphp
```typescript
import { Injectable, inject } from '@angular/core';
import { HttpClient, HttpHeaders, HttpParams } from '@angular/common/http';
import { Observable } from 'rxjs';

@Injectable({
    providedIn: 'root'
})
export class ApiService {

    private readonly http = inject(HttpClient);

    public request(): Observable<any> {
        const url = '{{ rtrim($baseUrl, "/") }}/{{$endpoint->uri}}';

@if(!empty($endpoint->headers))
        const headers = new HttpHeaders({
@foreach($endpoint->headers as $header => $value)
            '{{ $header }}': '{{ $value }}',
@endforeach
@empty($endpoint->headers['Accept'])
            'Accept': 'application/json',
@endempty
        });
@else
        const headers = new HttpHeaders({
            'Accept': 'application/json'
        });
@endif

@if(!empty($endpoint->cleanQueryParameters))
        const params = new HttpParams({
    @foreach($endpoint->cleanQueryParameters as $query => $value)
        '{{ $query }}': '{{ $value }}',
    @endforeach
    });
@else
        const params = new HttpParams();
@endif

@if(
    $endpoint->hasFiles() ||
    (
        isset($endpoint->headers['Content-Type']) &&
        $endpoint->headers['Content-Type'] === 'multipart/form-data' &&
        count($endpoint->cleanBodyParameters)
    )
)
        const body = new FormData();

@foreach($endpoint->cleanBodyParameters as $parameter => $value)
@foreach(u::getParameterNamesAndValuesForFormData($parameter, $value) as $key => $actualValue)
        body.append('{{ $key }}', '{{ $actualValue }}');
@endforeach
@endforeach

@foreach($endpoint->fileParameters as $parameter => $value)
@foreach(u::getParameterNamesAndValuesForFormData($parameter, $value) as $key => $file)
        body.append('{{ $key }}', fileInput.files![0]);
@endforeach
@endforeach

@elseif(count($endpoint->cleanBodyParameters))

@if(
    isset($endpoint->headers['Content-Type']) &&
    $endpoint->headers['Content-Type'] === 'application/x-www-form-urlencoded'
)
        const body = new HttpParams({
            fromObject: {!! json_encode($endpoint->cleanBodyParameters, JSON_UNESCAPED_UNICODE) !!}
        });
@else
        const body = {
        @foreach($endpoint->cleanBodyParameters as $body => $value)
    '{{ $body }}': '{{ $value }}',
        @endforeach
}
{{--        const body = {!! json_encode($endpoint->cleanBodyParameters, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) !!};--}}

@endif

@else
        const body = null;
@endif

        return this.http.request<any>(
           '{{ strtoupper($endpoint->httpMethods[0]) }}',
            url,
            {
                headers,
                params,
@if(
    $endpoint->hasFiles() ||
    count($endpoint->cleanBodyParameters)
)
                body,
@endif
                responseType: 'json'
            }
        );
    }
}
```
