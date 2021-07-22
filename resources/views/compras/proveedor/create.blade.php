@extends ('layouts.admin')
@section ('contenido')
	<div class="row">
		<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
			<h3>Nuevo Proveedor</h3>
			@if (count($errors)>0)
			<div class="alert alert-danger">
				<ul>
				@foreach ($errors->all() as $error)
					<li>{{$error}}</li>
				@endforeach
				</ul>
			</div>
			@endif
		</div>
	</div>		
			{!!Form::open(array('url'=>'compras/proveedor','method'=>'POST','autocomplete'=>'off'))!!}
            {{Form::token()}}
	<div class="row">
	    <div class="col-lg-6 col-sm-6 col-xs-12">
		   <div class="form-group">
            	<label for="nombre">Nombre</label>
            	<input type="text" name="nombre"require value="{{old('nombre')}}" class="form-control" placeholder="Nombre...">
            </div>
		</div>
		  <div class="col-lg-6 col-sm-6 col-xs-12">
		   <div class="form-group">
            	<label for="direccion">Direccion</label>
            	<input type="text" name="direccion"require value="{{old('direccion')}}" class="form-control" placeholder="Direccion...">
            </div>
		</div>
		<div class="col-lg-6 col-sm-6 col-xs-12">
		      <div class="form-group">
			     <label>Documento</label>
				 <select name="tipo_documento" class="form-control">
				    <option value="CC">CC</option>
					<option value="TI">TI</option>	
					<option value="PAS">PAS</option>	
				 </select>
			  </div>
		</div>
		<div class="col-lg-6 col-sm-6 col-xs-12">
		<div class="form-group">
            	<label for="num_documento">Numero documento</label>
            	<input type="text" name="num_documento"require value="{{old('num_documento')}}" class="form-control" placeholder="Numero documento....">
            </div>
		</div>
		<div class="col-lg-6 col-sm-6 col-xs-12">
		<div class="form-group">
            	<label for="telefono">Telefono</label>
            	<input type="text" name="telefono"require value="{{old('telefono')}}" class="form-control" placeholder="Telefono....">
            </div>
		</div>
		<div class="col-lg-6 col-sm-6 col-xs-12">
		<div class="form-group">
            	<label for="email">email</label>
            	<input type="email" name="email" require value="{{old('email')}}" class="form-control" placeholder="email...">
            </div>
		</div>
		<div class="col-lg-6 col-sm-6 col-xs-12">
		   <div class="form-group">
            	<button class="btn btn-primary" type="submit">Guardar</button>
				<button class="btn btn-danger" type="reset">Cancelar</button>

				{!!Form::close()!!}		
            <a href="/compras/proveedor"><button class="btn btn-success">Proveedores</button></a>

            </div>
		</div>
	</div>

	

			{!!Form::close()!!}	
@endsection