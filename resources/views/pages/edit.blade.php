@extends('layout')

@section('content')
<div class="container edit">
	<div class="row">
		<form class="form-horizontal" action="/edit/{{ $rep['_id'] }}" method="POST">
			{!! csrf_field() !!}
			<div class="panel panel-default">
				<div class="panel-body">
					<div class="form-group">
						<label for="name" class="col-sm-2 control-label">Name</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="name" value="{{ $rep['name'] ?? '' }}">
						</div>
					</div>
					<div class="form-group">
						<label for="first_name" class="col-sm-2 control-label">First Name</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="first_name" value="{{ $rep['first_name'] ?? '' }}">
						</div>
					</div>
					<div class="form-group">
						<label for="middle_name" class="col-sm-2 control-label">Middle Name</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="middle_name" value="{{ $rep['middle_name'] ?? '' }}">
						</div>
					</div>
					<div class="form-group">
						<label for="last_name" class="col-sm-2 control-label">Last Name</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="last_name" value="{{ $rep['last_name'] ?? '' }}">
						</div>
					</div>
					<div class="form-group">
						<label for="name_suffix" class="col-sm-2 control-label">Name Suffix</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="name_suffix" value="{{ $rep['name_suffix'] ?? '' }}">
						</div>
					</div>
					<div class="form-group">
						<label for="name_suffix" class="col-sm-2 control-label">Nickname</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="name_suffix" value="{{ $rep['nickname'] ?? '' }}">
						</div>
					</div>
					<div class="form-group">
						<label for="district" class="col-sm-2 control-label">District</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="district" value="{{ $rep['district'] ?? '' }}">
						</div>
					</div>
					<div class="form-group">
						<label for="division" class="col-sm-2 control-label">Division</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="division" value="{{ $rep['division'] ?? '' }}">
						</div>
					</div>
					<div class="form-group">
						<label for="office" class="col-sm-2 control-label">Office</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="office" value="{{ $rep['office'] ?? '' }}">
						</div>
					</div>
					<div class="form-group">
						<label for="title" class="col-sm-2 control-label">Title</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="title" value="{{ $rep['title'] ?? '' }}">
						</div>
					</div>
					<div class="form-group">
						<label for="party" class="col-sm-2 control-label">Party</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="party" value="{{ $rep['party'] ?? '' }}">
						</div>
					</div>
					<div class="form-group">
						<label for="photo" class="col-sm-2 control-label">Photo</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="photo" value="{{ $rep['photo'] ?? '' }}">
						</div>
					</div>
					<div class="form-group">
						<label for="state" class="col-sm-2 control-label">State</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="state" value="{{ $rep['state'] ?? '' }}">
						</div>
					</div>
				</div>
			</div>
			<div class="panel panel-default">
				<div class="panel-body">
					<div class="form-group">
						<label for="address" class="col-sm-2 control-label">Address</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="address[]" value="{{ $rep['address'][0] ?? '' }}">
							<input type="text" class="form-control" name="address[]" value="{{ $rep['address'][1] ?? '' }}">
							<input type="text" class="form-control" name="address[]" value="{{ $rep['address'][2] ?? '' }}">
						</div>
					</div>
					<div class="form-group">
						<label for="contact_form" class="col-sm-2 control-label">Contact Form</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="contact_form" value="{{ $rep['contact_form'] }}">
						</div>
					</div>
					<div class="form-group">
						<label for="phones" class="col-sm-2 control-label">Phones</label>
						<div class="col-sm-10">
							@for ($i = 0; $i < count($rep['phones']); $i++)
								<?php $phone = $rep['phones'][$i] ?>
								<div class="input-group">
									<input type="text" class="form-control" name="phones[]" value="{{ $phone }}">
									<span class="input-group-btn">
										<button class="btn {{ $i == 0 ? 'btn-add' : 'btn-minus' }}" type="button">
											<span class="glyphicon {{ $i == 0 ? 'glyphicon-plus' : 'glyphicon-minus' }}"></span>
										</button>
									</span>
								</div>
							@endfor
						</div>
					</div>
					<div class="form-group">
						<label for="emails" class="col-sm-2 control-label">Emails</label>
						<div class="col-sm-10">
							@if (!isset($rep['emails']) || count($rep['emails']) == 0)
								<div class="input-group">
									<input type="text" class="form-control" name="emails[]">
									<span class="input-group-btn">
										<button class="btn btn-add" type="button">
											<span class="glyphicon glyphicon-plus"></span>
										</button>
									</span>
								</div>
							@else
								@for ($i = 0; $i < count($rep['emails']); $i++)
									<div class="input-group">
										<input type="text" class="form-control" name="emails[]" value="{{ $rep['emails'][$i] }}">
										<span class="input-group-btn">
											<button class="btn {{ $i == 0 ? 'btn-add' : 'btn-minus' }}" type="button">
												<span class="glyphicon {{ $i == 0 ? 'glyphicon-plus' : 'glyphicon-minus' }}"></span>
											</button>
										</span>
									</div>
								@endfor
							@endif
						</div>
					</div>
					<div class="form-group">
						<label for="urls" class="col-sm-2 control-label">Urls</label>
						<div class="col-sm-10">
							@if (count($rep['urls']) == 0)
								<div class="input-group">
									<input type="text" class="form-control" name="urls[]">
									<span class="input-group-btn">
										<button class="btn {{ $i == 0 ? 'btn-add' : 'btn-minus' }}" type="button">
											<span class="glyphicon glyphicon-plus"></span>
										</button>
									</span>
								</div>
							@else
								@for ($i = 0; $i < count($rep['urls']); $i++)
									<?php $url = $rep['urls'][$i] ?>
									<div class="input-group">
										<input type="text" class="form-control" name="urls[]" value="{{ $url }}">
										<span class="input-group-btn">
											<button class="btn {{ $i == 0 ? 'btn-add' : 'btn-minus' }}" type="button">
												<span class="glyphicon {{ $i == 0 ? 'glyphicon-plus' : 'glyphicon-minus' }}"></span>
											</button>
										</span>
									</div>
								@endfor
							@endif
						</div>
					</div>
				</div>
			</div>
			<div class="panel panel-default">
				<div class="panel-body">
					<div class="form-group">
						<label for="facebook_id" class="col-sm-2 control-label">Facebook ID</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="facebook_id" value="{{ $rep['facebook_id'] ?? '' }}">
						</div>
					</div>
					<div class="form-group">
						<label for="twitter_id" class="col-sm-2 control-label">Twitter ID</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="twitter_id" value="{{ $rep['twitter_id'] ?? '' }}">
						</div>
					</div>
					<div class="form-group">
						<label for="youtube_id" class="col-sm-2 control-label">Youtube ID</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="youtube_id" value="{{ $rep['youtube_id'] ?? '' }}">
						</div>
					</div>
					<div class="form-group">
						<label for="google_id" class="col-sm-2 control-label">Google ID</label>
						<div class="col-sm-10">
							<input type="text" class="form-control" name="google_id" value="{{ $rep['google_id'] ?? '' }}">
						</div>
					</div>
				</div>
			</div>

			@if ($rep->reports()->count() > 0)
				<div class="panel panel-default">
					<div class="panel-body">
						<div class="checkbox">
							<label>
								<input type="checkbox" name="clear_reports" value="yes"> This Representative has been flagged for review! Clear these flags on save?
							</label>
						</div>
					</div>
				</div>
			@endif

			<div class="form-group">
				<div class="col-lg-12">
					<button type="submit" class="btn btn-success btn-block">Submit</button>
				</div>
			</div>
		</form>
	</div>
</div>
@stop

@section('scripts.body')
	<script src="https://code.jquery.com/jquery-2.2.0.min.js"></script>
	<script src="{{ URL::asset('js/edit.js') }}"></script>
@stop