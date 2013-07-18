<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');
Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');

class Calif_Views_Departamento {
	public function index ($request, $match) {
		$departamentos = Gatuf::factory ('Calif_Departamento')->getList ();
		
		$maestro_model = new Calif_Maestro ();
		$maestro_model->tabla = $maestro_model->special_tabla;
		$materia_model = new Calif_Materia ();
		
		$total_maestros = array ();
		$total_materias = array ();
		foreach ($departamentos as $d) {
			$sql = new Gatuf_SQL ('maestro_departamento=%s', $d->clave);
			$sql2 = new Gatuf_SQL ('departamento=%s', $d->clave);
			
			$total_maestros [$d->clave] = $maestro_model->getList (array ('count' => true, 'filter' => $sql->gen ()));
			$total_materias [$d->clave] = $materia_model->getList (array ('count' => true, 'filter' => $sql2->gen ()));
		}
		return Gatuf_Shortcuts_RenderToResponse ('calif/departamento/index.html',
		                                         array('page_title' => 'Departamentos',
                                                       'departamentos' => $departamentos,
                                                       'maestros' => $total_maestros,
                                                       'materias' => $total_materias),
                                                 $request);
	}
}
