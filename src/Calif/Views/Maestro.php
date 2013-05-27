<?php

Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Calif_Views_Maestro {
	public function index ($request, $match) {
		$maestro = new Calif_Maestro ();
		
		$pag = new Gatuf_Paginator ($maestro);
		$pag->action = array ('Calif_Views_Maestro::index');
		$pag->summary = 'Lista de maestros';
		$list_display = array (
			array ('codigo', 'Gatuf_Paginator_DisplayVal', 'Código'),
			array ('apellido', 'Gatuf_Paginator_DisplayVal', 'Apellido'),
			array ('nombre', 'Gatuf_Paginator_DisplayVal', 'Nombre'),
		);
		
		$pag->items_per_page = 50;
		$pag->no_results_text = 'No se encontraron maestros';
		$pag->max_number_pages = 5;
		$pag->configure ($list_display,
			array ('codigo', 'nombre', 'apellido'),
			array ('codigo', 'nombre', 'apellido')
		);
		
		$pag->setFromRequest ($request);
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/maestro/index.html',
		                                         array('page_title' => 'Maestros',
                                                       'paginador' => $pag),
                                                 $request);
	}
	
	public function verMaestro ($request, $match) {
		$maestro = new Calif_Maestro ();
		
		if (false === $maestro->getMaestro ($match[1])) {
			throw new Gatuf_HTTP_Error404();
		}
		
		$maestro->getSession();
		$sql = new Gatuf_SQL ('maestro=%s', $maestro->codigo);
		
		$grupos = Gatuf::factory ('Calif_Seccion')->getList (array ('filter' => $sql->gen ()));
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/maestro/ver-maestro.html',
		                                         array('page_title' => 'Ver maestro',
		                                               'maestro' => $maestro,
                                                       'grupos' => $grupos),
                                                 $request);
	}
}
