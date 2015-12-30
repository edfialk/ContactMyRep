<div class='row results'>
	<div class='col s12'>
		<table>
			<tbody>
				@foreach($reps as $rep)
					<tr>
						<td><a href='#'>{{$rep->title }} {{ $rep->printName() }}</a></td>
						<td>{{ isset($rep->district) ? ' District '.$rep->district : '' }}</td>
						<td>{{ $rep->phone or '' }}</td>
						<td>{{ $rep->address or '' }}</td>
						<td>
							@if (isset($rep->website))
								<a href="{{ $rep->website }}"><i class="fa fa-desktop"></i></a>
							@endif
							@if (isset($rep->contact_form))
								<a href="{{ $rep->contact_form }}"><i class="fa fa-envelope"></i></a>
							@elseif (isset($rep->email))
								<a href="mailto:{{ $rep->email }}"><i class="fa fa-envelope"></i></a>
							@endif
							@if (isset($rep->facebook_id))
								<a href="http://facebook.com/{{ $rep->facebook_id }}"><i class="fa fa-facebook-official"></i></a>
							@endif
							@if (isset($rep->twitter_id))
								<a href="http://twitter.com/{{ $rep->twitter_id }}"><i class="fa fa-twitter"></i></a>
							@endif
						</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
</div>

@if (count($reps) == 0)
<div class='alert'>No results for that query</div>
@endif