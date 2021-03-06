<?php

namespace App\Http\Controllers;

use App\CatalogoCurso;
use App\DiplomadosCurso;
use App\Coordinacion;
use App\Curso;
use App\ParticipantesCurso;
use App\ProfesoresCurso;
use App\Profesor;
use App\Salon;
use App\Http\Controllers\DB;
use Illuminate\Http\Request;
use Session;

class CursoController extends Controller
{
     /**
     *
     * @return \Illuminate\Http\Response
     */
        public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Depsliega los cursos programados

     * @return vista para consultar los Cursos Programados
     */
    public function vistaInstructores($id)
    {
        $curso = Curso::findOrFail($id);
        $profesores = Profesor::whereNotIn('id',
          ProfesoresCurso::select('profesor_id')->where('curso_id',$id)->get())->get();
        $instructores = Profesor::whereIn('id',
          ProfesoresCurso::select('profesor_id')->where('curso_id',$id)->get())->get();
        return view('pages.curso-inscribir-instructores')
          ->with('curso', $curso)
          ->with('profesores', $profesores)
          ->with('instructores', $instructores);
    }

    public function altaInstructores($curso_id, $profesor_id)
    {
        $instructor = new ProfesoresCurso;
        $instructor->curso_id = $curso_id;
        $instructor->profesor_id = $profesor_id;
        $instructor->save();
        return redirect()->route('curso.modificarInstructores', $curso_id)
          ->with('success', "El profesor ahora es instructor");
    }

    public function bajaInstructores($curso_id, $profesor_id)
    {
        $instructor = ProfesoresCurso::where('curso_id', $curso_id)->where('profesor_id',$profesor_id);
        $instructor->delete();
        return redirect()->route('curso.modificarInstructores', $curso_id)
          ->with('warning', "El profesor ya no es más un instructor del curso");
    }
    
     public function index()
    {
        return view("pages.consulta-cursos")
            ->with("cursos",Curso::all());
    }

    /**
     * Despliega la vista para añadir un curso
     *
     *  @param type $var Description.
     *
     * @return Despliega la vista para añadir un nuevo curso

     */
    public function nuevo($id)
    {
        $user = CatalogoCurso::find($id);
        $salones = Salon::all();
        $profesores = Profesor::all();
        return view("pages.alta-curso")
            ->with("salones",$salones)
            ->with("profesores",$profesores)
            ->with("user",$user);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Curso::find($id);
        $profesores = Profesor::whereIn('id', ProfesoresCurso::select('profesor_id')->where('curso_id',$id)->get())->get();
        return view("pages.ver-curso")
            ->with("user",$user)
            ->with("profesores", $profesores);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = Curso::find($id);
        $repetidos = ProfesoresCurso::select('profesor_id')->where('curso_id',$id)->get();
        $repeArray = array();
        foreach ($repetidos as $value) {
            array_push($repeArray, $value->profesor_id);
        }
        return view("pages.update-curso")
            ->with("user",$user)
            ->with("cursos",Curso::all())
            ->with("profesores", Profesor::all())
            ->with("repetidos", $repeArray);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    
        $user = Curso::find($id);
        $user->semestre_anio = $request->semestreAnio;
        $user->semestre_pi = $request->semestreTemporada;
        $user->semestre_si = $request->semestreInter;
        $user->catalogo_id = $request->catalogo_id;
        $user->fecha_inicio = $request->fecha_inicio;
        $user->fecha_fin = $request->fecha_fin;
        $user->hora_inicio = $request->hora_inicio;
        $user->hora_fin = $request->hora_fin;
        $user->dias_semana = $request->dias_semana;
        $user->numero_sesiones = $request->numero_sesiones;
        $user->acreditacion = $request->acreditacion;
        $user->costo = $request->costo;
        $user->cupo_maximo = $request->cupo_maximo;
        $user->cupo_minimo = $request->cupo_minimo;
        $user->salon_id = $request->salon_id;
        $user->save();
        $catalogo = CatalogoCurso::find($user->catalogo_id);
        if($catalogo->tipo == 'S'){
            return redirect()
                ->route('profesorts.update', $user->id);
        }
        else{
          return redirect('curso')
            ->with('success', 'Se han actualizado los datos correctamente');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function search(Request $request)
    {
        if($request->type == "nombre")
            {
                $words=explode(" ", $request->pattern);
                foreach($words as $word){
                    $users = Curso::whereRaw("lower(unaccent(nombre)) ILIKE lower(unaccent('%".$word."%'))")
                    -> get();
                }
             return view('display')->with("users",$users);
        }elseif($request->type == "rfc")
        {
                $users = Profesor::whereRaw("lower(unaccent(rfc)) ILIKE lower(unaccent('%".$request->pattern."%'))")
                ->get();
                return view('display')->with("users",$users);
        }elseif($request->type == "email"){
                $users = Profesor::whereRaw("lower(unaccent(email)) ILIKE lower(unaccent('%".$request->pattern."%'))")
                ->get();
                return view('display')->with("users",$users);
        }elseif($request->type == "telefono"){
                $users = Profesor::whereRaw("lower(unaccent(telefono)) ILIKE lower(unaccent('%".$request->pattern."%'))")
                ->get();
                return view('pages.consulta-cursos')->with("users",$users);
        }

    }

    public function searchWords($id){
      return view('pages.buscador-tematicas')->with('curso_id', $id);
    }
    public function Csearch(Request $request){
       
        if ($request->type == "nombre_curso") {
            $catalogos_res = CatalogoCurso::select('id')->whereRaw("lower(unaccent(nombre_curso)) ILIKE lower(unaccent('%".$request->pattern."%'))")->get();
            $res_busqueda = Curso::whereIn('catalogo_id', $catalogos_res)
                ->get();
            return view('pages.consulta-cursos')->with("cursos",$res_busqueda);
        }elseif ($request->type=="fechas") {
            $aux = collect();
            switch ($request->Sem.$request->IO) {
                case '1i':
                    $aux->push(Curso::select('id')->where("semestre_anio",$request->anio)->where("semestre_pi",'1')->where("semestre_si",'s')->get());
                    break;
                case '2s':
                    $aux->push(Curso::select('id')->where("semestre_anio",$request->anio)->where("semestre_pi",'1')->where("semestre_si",'s')->get());
                    $aux->push(Curso::select('id')->where("semestre_anio",$request->anio)->where("semestre_pi",'1')->where("semestre_si",'i')->get());
                    break;
                case '2i':
                    $aux->push(Curso::select('id')->where("semestre_anio",$request->anio)->where("semestre_pi",'1')->where("semestre_si",'s')->get());
                    $aux->push(Curso::select('id')->where("semestre_anio",$request->anio)->where("semestre_pi",'1')->where("semestre_si",'i')->get());
                    $aux->push(Curso::select('id')->where("semestre_anio",$request->anio)->where("semestre_pi",'2')->where("semestre_si",'s')->get());
                    break;
                default:
                    break;
            }
            switch ($request->Sem2.$request->IO2) {
                case '1s':
                    $aux->push(Curso::select('id')->where("semestre_anio",$request->anio2)->where("semestre_pi",'1')->where("semestre_si",'i')->get());
                    $aux->push(Curso::select('id')->where("semestre_anio",$request->anio2)->where("semestre_pi",'2')->where("semestre_si",'i')->get());
                    $aux->push(Curso::select('id')->where("semestre_anio",$request->anio2)->where("semestre_pi",'2')->where("semestre_si",'s')->get());
                    break;
                case '1i':
                    $aux->push(Curso::select('id')->where("semestre_anio",$request->anio2)->where("semestre_pi",'2')->where("semestre_si",'i')->get());
                    $aux->push(Curso::select('id')->where("semestre_anio",$request->anio2)->where("semestre_pi",'2')->where("semestre_si",'s')->get());
                    break;
                case '2s':
                    $aux->push(Curso::select('id')->where("semestre_anio",$request->anio2)->where("semestre_pi",'2')->where("semestre_si",'i')->get());
                    break;
                default:
                    break;
            }
            $res_busqueda = Curso::where("semestre_anio", ">=", (integer)$request->anio)->where("semestre_anio", "<=", (integer)$request->anio2)
                ->whereNotIn("id",$aux->filter(function ($value, $key){return sizeof($value) > 0;}))
                ->get();
            return view('pages.consulta-cursos')->with("cursos",$res_busqueda);
        }elseif ($request->type=="titular") {
            $words=explode(" ", $request->pattern);
            foreach($words as $word){
                $profesores = Profesor::select('id')->whereRaw("lower(unaccent(nombres)) ILIKE lower(unaccent('%".$word."%'))")
                ->orWhereRaw("lower(unaccent(apellido_paterno)) ILIKE lower(unaccent('%".$word."%'))")
                ->orWhereRaw("lower(unaccent(apellido_materno)) ILIKE lower(unaccent('%".$word."%'))")
                ->get();
            }
            $curso_prof = ProfesoresCurso::select('curso_id')->whereIn('profesor_id', $profesores)->get();
            $res_busqueda = Curso::whereIn('id',$curso_prof)->get();
            return view('pages.consulta-cursos')->with("cursos",$res_busqueda);
        }elseif ($request->type=="clave") {
            $catalogos_res = CatalogoCurso::select('id')->whereRaw("lower(unaccent(clave_curso)) ILIKE lower(unaccent('%".$request->pattern."%'))")->get();
            $res_busqueda = Curso::whereIn('catalogo_id', $catalogos_res)
                ->get();
            return view('pages.consulta-cursos')->with("cursos",$res_busqueda);
        }
    }

    public function delete($id)
    {
        $participantes = ParticipantesCurso::where('curso_id', $id)->get();
        if(empty($participantes[0]) == FALSE){
            return redirect()->back()->with('warning', 'El curso no puede ser eliminado porque tiene alumnos inscritos.');
        }
        $profesores = ProfesoresCurso::where('curso_id', $id)->get();
        foreach($profesores as $profesor){
            $profesor->delete();
        }
        $user = Curso::findOrFail($id);
        $user -> delete(); 
        return redirect('curso')->with('success', "El curso se eliminó exitosamente");
    }
    public function deleteModulo($id)
    {
        $participantes = ParticipantesCurso::where('curso_id', $id)->get();
        if(empty($participantes[0]) == FALSE){
            return redirect()->back()->with('danger', 'El curso no puede ser eliminado porque tiene alumnos inscritos.');
        }
        $profesores = ProfesoresCurso::where('curso_id', $id)->get();
        foreach($profesores as $profesor){
            $profesor->delete();
        }
        $user = Curso::findOrFail($id);
        $user -> delete(); 
        return redirect('diplomado')->with('success', "El módulo se eliminó exitosamente");
    }
    public function bajaParticipante($id,$curso,$espera)
    {
        try{
					$user = ParticipantesCurso::where('participante_curso.profesor_id',$id)
        ->where('participante_curso.curso_id',$curso)
        ->delete();
        $limite = ParticipantesCurso::where('curso_id',$curso)
            ->max('espera');
        if ($espera>0){
            for ($x=$espera+1; $x <= $limite; $x++) {
                ParticipantesCurso::where('espera',$x)->update(['espera'=>$x-1]);
            }
        }
        return redirect()->back()->with('success', 'El profesor ha sido desinscrito del curso');
			}catch (\Illuminate\Database\QueryException $e){
				return redirect()->back()->with('danger', 
					'El instructor no puede ser dado de baja porque ya contestó encuestas de evaluación.');
		}
    }


    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
        /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $dias_semana = '';
        if ($request->L == 'on') {
            if (strlen($dias_semana)>0) {
                $dias_semana .= ', Lunes';
            }else{$dias_semana .= 'Lunes';}
        }
        if ($request->M == 'on') {
            if (strlen($dias_semana)>0) {
                $dias_semana .= ', Martes';
            }else{$dias_semana .= 'Martes';}
        }
        if ($request->X == 'on') {
            if (strlen($dias_semana)>0) {
                $dias_semana .= ', Miércoles';
            }else{$dias_semana .= 'Miércoles';}
        }
        if ($request->J == 'on') {
            if (strlen($dias_semana)>0) {
                $dias_semana .= ', Jueves';
            }else{$dias_semana .= 'Jueves';}
        }
        if ($request->V == 'on') {
            if (strlen($dias_semana)>0) {
                $dias_semana .= ', Viernes';
            }else{$dias_semana .= 'Viernes';}
        }
        if ($request->S == 'on') {
            if (strlen($dias_semana)>0) {
                $dias_semana .= ', Sábado';
            }else{$dias_semana .= 'Sábado';}
        }


        $curso = new Curso;
        $profesorCurso = new ProfesoresCurso;

        $curso->semestre_anio = $request->semestreAnio;
        $curso->semestre_pi = (string)$request->semestreTemporada;
        $curso->semestre_si = $request->semestreInter;
        $curso->fecha_inicio = $request->fecha_inicio;
        $curso->fecha_fin = $request->fecha_fin;
        $curso->hora_inicio = $request->hora_inicio;
        $curso->hora_fin = $request->hora_fin;
        $curso->dias_semana = $dias_semana;
        $curso->numero_sesiones = $request->numero_sesiones;
        $curso->acreditacion = $request->acreditacion;
        $curso->costo = $request->costo;
        $curso->cupo_maximo = $request->cupo_maximo;
        $curso->cupo_minimo = $request->cupo_minimo;
        $curso->catalogo_id = $request->catalogo_id;
        $curso->salon_id = $request->salon_id;

        $curso->save();

        //Determinar el id del curso que se acaba de inscribir
        $newCurso = Curso::where('salon_id',$request->salon_id)
        ->where('catalogo_id',$request->catalogo_id)
        ->where('fecha_inicio',$request->fecha_inicio)
        ->where('fecha_fin',$request->fecha_fin)
        ->where('semestre_anio',$request->semestreAnio)
        ->where('semestre_pi', (string)$request->semestreTemporada)
        ->where('semestre_si',$request->semestreInter)
        ->get();
        $catalogo = CatalogoCurso::find($request->catalogo_id);
        return redirect()->route('curso.modificarInstructores', $curso->id)
          ->with('warning', 'Asigne instructores ahora o posteriormente');
    }
    public function inscripcionParticipante($id)
    { 
      //Datos del curso y cantidad de participantes
      $curso = Curso::findOrFail($id);
      $count = ParticipantesCurso::select('id')
          ->where('curso_id',$id)
          ->count();
      
      //Profesores que se pueden inscribir al curso
      $users = Profesor::select('*')
          ->whereNotIn('id',Profesor::join('participante_curso','participante_curso.profesor_id','profesors.id')
              ->where('participante_curso.curso_id',$id)
              ->select('profesors.id')->get())
          ->whereNotIn('id', ProfesoresCurso::where('curso_id',$id)->select('profesor_id')->get())
          ->get()
          ->sortBy("apellido_paterno");

      //Cancelados y lista de espera
      $enLista = ParticipantesCurso::select('id')->where('estuvo_en_lista',true)->where('curso_id',$id)->count();
      $cancelados = ParticipantesCurso::select('id')->where('cancelación',true)->where('curso_id',$id)->count();
      
      $countAux = 0;
      if($count > ($enLista+$cancelados))
          $countAux = $count-$enLista-$cancelados;
      return view("pages.curso-inscripcion")
          ->with("users",$users)
          ->with("count",$countAux)
          ->with("curso",$curso)
          ->with("lista",$enLista);
    }

    public function GenerarFormatos($id)
    {
        $users = Profesor::leftJoin('participante_curso','profesors.id','=','participante_curso.profesor_id')
            ->where('participante_curso.curso_id',$id)
            ->select('profesors.*')->get();

        $curso = Curso::findOrFail($id);

        return view("pages.curso-generarformatos")
            ->with("users",$users)
            ->with("curso",$curso);
    }

    public function verParticipante($id)
    {
        $curso = Curso::findOrFail($id);

        $users = Profesor::leftJoin('participante_curso','profesors.id','=','participante_curso.profesor_id')
            ->where('participante_curso.curso_id',$id)
            ->select('profesors.*')->get();
        $users = $users->sortBy(function($user){
                return $user->apellido_paterno;
            });

        $lista_curso=array();
        foreach($users as $user){
            $participanteCurso = ParticipantesCurso::where('curso_id',$id)
            ->where('profesor_id',$user->id)->get();
            array_push($lista_curso,$participanteCurso[0]);
        }
        //$lista_curso = ParticipantesCurso::where('curso_id',$id)
        //    ->select('participante_curso.*')->get();



        //ParticipantesCurso::where('participante_curso.curso_id', $id)->get();
        /*$lista_curso = $lista_curso->sortBy(function($participante){
                return $participante->profesor_id;
            });*/

        $tmp = array();
        foreach($users as $user){
                $tmp[] = $user;
        }
        $users = $tmp;

        /*$tmp2 = array();
        foreach($lista_curso as $participante){
            $tmp2[] = $participante;
        }
        $lista_curso = $tmp2;*/
        //return $users;
        //return $lista_curso;

        return view("pages.curso-ver-profesores")
            ->with("users",$users)
            ->with("curso",$curso)
            ->with("participantes",$lista_curso);
    }



    public function verRespuesta(Request $request){
    //dd($request);
    //return $request;
    //Crear una lista únicamente de los alumnos que no cancelaron
    $alumnos=array();
    $enFila = array();
    for ($i = 0; $i < sizeof($request->alumnos); $i++){
        if(!$request->cancelaciones or ($request->cancelaciones and !in_array($request->alumnos[$i], $request->cancelaciones))){
            array_push($alumnos,$request->alumnos[$i]);
            array_push($enFila,$request->aux[$i]);
        }
    }

    //return $alumnos;
    //return array_diff($alumnos,$request->cancelaciones);

    /*Checar que el registro de los alumnos que acaban de cancelar quede marcado
    foreach($request->alumnos as $alumno){
        if(in_array($alumno,$request->cancelaciones) and !in_array($alumno,$alumnos)){
            $participantes = ParticipantesCurso::where('curso_id',$request->curso_id)
            ->where('profesor_id',$alumno)
            ->get();
            $participante = $participantes[0];
            $participante->cancelación = true;
            $participante->save();
        }
    }*/
    //return $request["calificaciones"];

    if($request->cancelaciones){
        foreach($request->cancelaciones as $cancelacion){
            $participantes = ParticipantesCurso::where('curso_id',$request->curso_id)
            ->where('profesor_id',$cancelacion)
            ->get();
            $participante = $participantes[0];
            $participante->cancelación = true;
            $participante->estuvo_en_lista = false;
            $participante->espera = 0;
            $participante->save();
        }
    }

    for ($i = 0; $i < sizeof($alumnos); $i++){
        $profesor_id = $alumnos[$i];

        $participantes = ParticipantesCurso::where('curso_id',$request->curso_id)
        ->where('profesor_id',$profesor_id)
        ->get();

        $participante = $participantes[0];
        $calificacion = $request["calificaciones"][$i];
        $causa_no_acreditacion = $request["causa_no_acreditacion"][$i];
        $monto_pago = $request["monto_pago"][$i];
        $comentario = $request["comentario"][$i];

        $participante->calificacion = $calificacion;
        $participante->causa_no_acreditacion = $causa_no_acreditacion;
        $participante->monto_pago = $monto_pago;
        $participante->comentario = $comentario;
        $participante->espera = (int)$enFila[$i];

        if($request->confirmaciones){
            if(in_array($profesor_id,$request->confirmaciones)){
                $participante->confirmacion = true;
            }else{
                $participante->confirmacion = false;
            }
        }else{
            $participante->confirmacion = false;

        }


        if($request->estuvo_en_lista){
            if(in_array($profesor_id,$request->estuvo_en_lista)){
                $participante->estuvo_en_lista = true;
            }else{
                $participante->estuvo_en_lista = false;
            }
        }else{
            $participante->estuvo_en_lista = false;

        }

        if($request->cancelaciones){
            if(in_array($profesor_id,$request->cancelaciones)){
                $participante->cancelación = true;
            }else{
                $participante->cancelación = false;
            }
        }else{
            $participante->cancelación = false;

        }

        if($request->asistencia){
            if(in_array($profesor_id,$request->asistencia)){
                $participante->asistencia = true;
            }else{
                $participante->asistencia = false;
            }
        }else{
            $participante->asistencia = false;

        }

        if($request->acreditacion){
            if(in_array($profesor_id,$request->acreditacion)){
                $participante->acreditacion = true;
            }else{
                $participante->acreditacion = false;
            }
        }else{
            $participante->acreditacion = false;
        }


        if($request->hoja_evaluacion){
            if(in_array($profesor_id,$request->hoja_evaluacion)){
                $participante->contesto_hoja_evaluacion = true;
            }else{
                $participante->contesto_hoja_evaluacion = false;
            }
        }else{
            $participante->contesto_hoja_evaluacion = false;
        }


        if($request->pago_curso){
            if(in_array($profesor_id,$request->pago_curso)){
                $participante->pago_curso = true;
            }else{
                $participante->pago_curso = false;
            }
        }else{
            $participante->pago_curso = false;
        }
        if($request->adicional){
            if(in_array($profesor_id,$request->adicional)){
                $participante->adicional = true;
            }else{
                $participante->adicional = false;
            }
        }else{
            $participante->adicional = false;

        }

            $participante->save();
        }
            $users = Curso::all();
            return redirect()->back()->with('success', 'Cambios realizados correctamente');
    }

    public function registrarParticipante(Request $request){
        $count = ParticipantesCurso::select('id')
            ->where('curso_id',$request->curso_id)
            ->count();
        $enLista = ParticipantesCurso::select('id')
            ->where('curso_id',$request->curso_id)
            ->where('estuvo_en_lista',true)
            ->count();
        $cancelados = ParticipantesCurso::select('id')
            ->where('curso_id',$request->curso_id)
            ->where('cancelación',true)
            ->count();
        $cupo = Curso::findOrFail($request->curso_id)->cupo_maximo;
//Queda pendiente el registro
        if($count-$enLista-$cancelados < $cupo){

            $user = new ParticipantesCurso;
            $user->curso_id = $request->curso_id;
            $user->profesor_id = $request->id;
            $user->espera = 0;
            $user->estuvo_en_lista = false;
            $user->save();
            return redirect()->route("curso.inscripcion",$request->curso_id)
              ->with('success', 'Se ha dado de alta correctamente');

        }else{
            $user = new ParticipantesCurso;
            $user->curso_id = $request->curso_id;
            $user->profesor_id = $request->id;
            $user->espera = $enLista+1;
            $user->estuvo_en_lista = true;
            $user->save();
            return redirect()->route("curso.inscripcion",$request->curso_id)
              ->with('success', 'Inscripción en lista de espera');
        }

    }
}
