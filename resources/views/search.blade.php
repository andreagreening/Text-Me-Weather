@extends('layouts.app')

@section('content')
<div class="container">
	<div class="row">
		<div class="col-sm-8 col-sm-offset-2">
			<div class="panel panel-default">
				<div class="panel-heading">Find the current weather</div>
				<div class="panel-body">
					<form action="{{ route('get.weather') }}" method="GET">
							{{ csrf_field() }}

						<div class="form-group">
							<label for="zip">
								Enter the Zip Code
							</label>
							<input type="text" name="zip" placeholder="example: 90210">
							<input type="submit" class="btn btn-default">
						</div>	
						</form>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection
