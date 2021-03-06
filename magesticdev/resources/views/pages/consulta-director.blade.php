@extends('layouts.principal')

@section('contenido')
        <!--Body content-->

<div class="content">
    <div class="top-bar">
        <a href="#menu" class="side-menu-link burger">
            <span class='burger_inside' id='bgrOne'></span>
            <span class='burger_inside' id='bgrTwo'></span>
            <span class='burger_inside' id='bgrThree'></span>
        </a>
    </div>
    <section class="content-inner">
        <br>
        @include('partials.messages')
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3>Dirección</h3>

            </div>
        </div>
        <div class="panel-body">
            <table class="col-md-12">
            @if($user->id != null)
                <tr>
                    <th>Director</th>
                    <th>Género</th>
                </tr>
                <tr>
                    <td>{{ $user->grado}} {{ $user->director}}</td>
                    @if($user->genero === 'F')
                      <td>Femenino</td>
                    @elseif($user->genero === 'M')
                      <td>Masculino</td>
                    @endif
                </tr>
                <tr>
                    <td>
                        <a href="{{ URL::to('direccion/actualizar', $user->id) }}" style="margin: 10px;" class="btn btn-info">Editar</a>
                        <button type="button" class="btn btn-danger" data-toggle="modal" style="margin: 10px;" data-target="#myModal{{$user->id}}">Eliminar</button>
                    </td>
                </tr>
                @else
                <tr>
                    <th>No hay un director aún.</th>
                </tr>
                <tr>
                    <td>
                        <a href="{{route('direccion.nuevo')}}" style="margin: 10px;" class="btn btn-success">Nuevo</a>
                    </td>
                </tr>
                @endif
                <!-- Modal -->
                    <div class="modal fade" id="myModal{{$user->id}}" role="dialog">
                      <div class="modal-dialog">
                        <!-- Modal content-->
                        <div class="modal-content">
                          <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Eliminar Director</h4>
                          </div>
                          <div class="modal-body">
                            <p>¿Está seguro de eliminar al director {{$user->director}}?</p>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-normal" data-dismiss="modal" aria-label="Close">Cancelar</button>
                            <a href="{{ URL::to('direccion/baja', $user->id) }}" class="btn btn-danger">Eliminar</a>
                          </div>
                        </div>
                      </div>
                    </div>
            </table>
        </div>

    </section>

@endsection