<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Calif_Views_Departamento {
	public function index ($request, $match) {
		$departamentos = Gatuf::factory ('Calif_Departamento')->getList ();
		
		$maestro_model = new Calif_Maestro ();
		$materia_model = new Calif_Materia ();
		$seccion_model = new Calif_Seccion ();
		
		$total_maestros = array ();
		$total_materias = array ();
		$total_secciones = array ();
		foreach ($departamentos as $d) {
			$sql = new Gatuf_SQL ('departamento=%s', $d->clave);
			$sql2 = new Gatuf_SQL ('materia_departamento=%s', $d->clave);
			
			$total_maestros [$d->clave] = $maestro_model->getList (array ('view' => 'maestros_departamentos', 'count' => true, 'filter' => $sql->gen ()));
			$total_materias [$d->clave] = $materia_model->getList (array ('count' => true, 'filter' => $sql->gen ()));
			$total_secciones [$d->clave] = $seccion_model->getList (array ('view' => 'paginador', 'count' => true, 'filter' => $sql2->gen ()));
		}
		return Gatuf_Shortcuts_RenderToResponse ('calif/departamento/index.html',
		                                         array('page_title' => 'Departamentos',
                                                       'departamentos' => $departamentos,
                                                       'maestros' => $total_maestros,
                                                       'materias' => $total_materias,
                                                       'secciones' => $total_secciones),
                                                 $request);
	}
	
	public function buscarErrorHoras ($request, $match) {
				$departamentos =  Gatuf::factory ('Calif_Departamento')->getList();
				
					return Gatuf_Shortcuts_RenderToResponse ('calif/departamento/buscar-errores-horas.html',
		                                          array ('departamentos' => $departamentos,
		                                                 'page_title' => 'Busar Errores de horas por Departamento'),
				                                          $request);
		}

	public $agregarDepartamento_precond = array ('Calif_Precondition::jefeRequired');
	public function agregarDepartamento ($request, $match) {
		$title = 'Nuevo departamento';
		
		$extra = array ();
		$extra['user'] = $request->user;
		if ($request->method == 'POST') {
			$form = new Calif_Form_Departamento_Agregar ($request->POST, $extra);
			
			if ($form->isValid()) {
			$departamento = $form->save();
				
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Departamento::index');
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Departamento_Agregar (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/departamento/agregar-departamento.html',
		                                         array ('page_title' => $title,
		                                                'form' => $form),
		                                         $request);
	}
}
