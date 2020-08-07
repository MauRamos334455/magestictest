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
        <div class="panel panel-default">
            <div class="panel-heading">
            @include ('partials.messages')
                <h3>Coordinador general</h3>

            </div>
        </div>
        <div class="panel-body">
            <table class="col-md-12 container-fluid">
                <tr>
                    <th>Coordinador</th>
                
                </tr>
                <tr>
                    <td>{{ $user->grado}} {{ $user->coordinador}}</td>
                </tr>
                <tr>
                    <td>
                        <a href="{{route('coordinador-general.nuevo')}}" style="margin: 10px;" class="btn btn-success">Nuevo</a>

                    </td>
                    @if($user->id != null)
                    <td>
                        <a href="{{ URL::to('coordinador-general/actualizar', $user->id) }}" style="margin: 10px;" class="btn btn-info">Editar</a>
                        <button type="button" class="btn btn-danger" data-toggle="modal" style="margin: 10px;" data-target="#myModal{{$user->id}}">Eliminar</button>
                    </td>
                    @endif
                </tr>

                <!-- Modal -->
                    <div class="modal fade" id="myModal{{$user->id}}" role="dialog">
                      <div class="modal-dialog">
                        <!-- Modal content-->
                        <div class="modal-content">
                          <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <h4 class="modal-title">Eliminar Coordinador</h4>
                          </div>
                          <div class="modal-body">
                            <p>¿Está seguro de eliminar al coordinador {{ $user->coordinador}}?</p>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-normal" data-dismiss="modal" aria-label="Close">Cancelar</button>
                            <a href="{{ URL::to('coordinador-general/baja', $user->id) }}" class="btn btn-danger">Eliminar</a>
                          </div>
                        </div>
                      </div>
                    </div>

            </table>
        </div>

    </section>

@endsection