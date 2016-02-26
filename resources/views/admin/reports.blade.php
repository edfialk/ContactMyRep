@extends('layout')

@section('content')
	<div class="container">
		<div class="row">
			<table class="table">
				<thead>
					<tr>
						<th>Representative</th><th>Number of Reports</th>
					</tr>
				</thead>
				<tbody>
					@foreach($reps as $rep)
						<tr>
							<td><a href='{{ route('editrep', ['id' => $rep->id, 'redirect' => 'reports']) }}'>{{ $rep->name }}</a></td>
							<td>{{ $rep->reports()->count() }}</td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
@stop
