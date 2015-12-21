@extends('layout')

@section('content')

@if (count($reps) == 0)
<div class='alert'>No results for that zipcode</div>
@endif

@foreach ($reps as $rep)
<div class='rep'>
	<h3>{{ $rep->name }}</h3>
	<h5>{{ $rep->title }} from {{ $rep->state_name }} {{ isset($rep->district) ? 'District '.$rep->district : '' }}</h5>
	@if (isset($rep->office))
		<h5>{{ $rep->office }}</h5>
	@endif
	@if (isset($rep->phone))
		<h5>{{ $rep->phone }}</h5>
	@endif
	@if (isset($rep->website))
		<h5><a href='{{ $rep->website }}'>{{ $rep->website }}</a></h5>
	@endif
	<ul>
		@if (isset($rep->bioguide_id))
			<li><a href='http://bioguide.congress.gov/scripts/biodisplay.pl?index={{ $rep->bioguide_id}}'>Bioguide</a></li>
			<li><a href='https://www.congress.gov/member/{{ $rep->first_name }}-{{ $rep->last_name }}/{{ $rep->bioguide_id }}'>Congress.gov</a></li>
		@endif
		@if (isset($rep->birthday))
			<li>Birthday: {{ $rep->birthday }}</li>
		@endif
		@if (isset($rep->contact_form))
			<li><a href='{{ $rep->contact_form }}'>Contact Form</a></li>
		@endif
		@if (isset($rep->facebook_id))
			<li><a href='http://facebook.com/{{ $rep->facebook_id }}'>Facebook</a></li>
		@endif
		@if (isset($rep->fec_id))
			<li><a href='http://www.fec.gov/fecviewer/CandidateCommitteeDetail.do?candidateCommitteeId={{ $rep->fec_id }}&amp;tabIndex=1'>FEC</a></li>
		@endif
		@if (isset($rep->govtrack_id))
			<li><a href='https://www.govtrack.us/congress/members/{{ $rep->govtrack_id }}'>GovTrack</a></li>
		@endif
		@if (isset($rep->crp_id))
			<li><a href='http://www.opensecrets.org/politicians/summary.php?cid={{ $rep->crp_id }}'>OpenSecrets</a></li>
		@endif
		@if (isset($rep->twitter_id))
			<li><a href='http://twitter.com/{{ $rep->twitter_id }}'>Twitter</a></li>
		@endif
		@if (isset($rep->votesmart_id))
			<li><a href='http://votesmart.org/candidate/biography/{{ $rep->votesmart_id }}'>Vote Smart</a>
		@endif
		@if (isset($rep->youtube_id))
			<li><a href='http://youtube.com/{{ $rep->youtube_id }}'>YouTube</a></li>
		@endif
	</ul>
</div>
@endforeach

@stop