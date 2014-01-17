<?php

class Calif_PDF_CargaHoraria extends External_FPDF {
	public $profesor;
	public $departamento;
	public $oficio;
	
	function renderBase () {
		setLocale(LC_ALL, 'es_MX.UTF-8');
		
		$this->AddFont ('Georgia');
		$this->AddFont ('Georgia', 'b');
		
		$this->SetFont ('Georgia', '', 10);
		$this->AddPage ('P', 'Letter');
		$this->SetMargins (0, 0, 0);
		
		$this->Image (dirname(__FILE__).'/data/membretes/membrete_cabecera_'.$this->departamento->clave.'.jpg', 0, 0, 216, 0);
		
		$this->SetY (40);
		$this->SetX (36);
		
		$this->Cell (160, 0, 'CUCEI/DIVEC/DCC/'.$this->oficio.'/2014', 0, 0, 'R');
		
		$this->SetY (50);
		$this->SetX (36);
		
		$this->SetFont ('Georgia', 'b', 10);
		$this->CellSmallCaps (70, 0, 'A Quien Corresponda', 0, 0, 'L');
		
		$this->SetFont ('Georgia', '', 10);
		
		$this->SetY (60);
		$this->SetX (36);
		
		$this->Cell (160, 0, 'Por este medio se hace constar que el profesor:', 0, 0, 'C');
		
		$this->SetFont ('Georgia', 'b', 10);
		$this->SetY (68);
		$this->SetX (36);
		$texto = (string) $this->profesor;
		$this->Cell (160, 0, $texto, 0, 0, 'C');
		
		$this->SetFont ('Georgia', '', 10);
		$this->SetY (74);
		$this->SetX (36);
		$texto = 'Adscrito al '.$this->departamento->descripcion.' de este Centro Universitario, durante el ciclo escolar 2014A, tendrá asignados los siguientes cursos:';
		$this->MultiCell (160, 5, $texto);
		
		$this->SetY (88);
		$this->SetX (36);
		
		$w = array (13, 13, 96, 21, 21);
		$headers = array ('NRC', 'Clave', 'Materia', 'Sección', 'Hrs/Sem');
		
		$this->SetFont ('Georgia', 'b', 10);
		for ($g = 0; $g < count ($headers); $g++) {
			$this->Cell ($w[$g], 6, $headers[$g], 1, 0, 'C');
		}
		
		$this->SetFont ('Georgia', '', 10);
		$this->Ln ();
		
		if ($this->departamento->clave == 1500 || $this->departamento->clave == 1510) {
			$sql = new Gatuf_SQL ('(materia_departamento=%s OR materia_departamento=%s)', array (1500, 1510));
		} else {
			$sql = new Gatuf_SQL ('materia_departamento=%s', $this->departamento->clave);
		}
		$secciones = $this->profesor->get_calif_seccion_list (array ('view' => 'paginador', 'filter' => $sql->gen ()));
		
		foreach ($secciones as $seccion) {
			$save_y = $this->GetY ();
			if ($this->GetStringWidth ($seccion->materia_desc) > $w[2]) {
				$alto = 10;
			} else {
				$alto = 8;
			}
			$this->SetX (36);
			$this->Cell ($w[0], $alto, str_pad ($seccion->nrc, 5, '0', STR_PAD_LEFT), 1, 0, 'C');
			
			$this->Cell ($w[1], $alto, $seccion->materia, 1, 0, 'C');
			
			$this->Cell ($w[2], $alto, '', 1, 0, 'C');
			
			if ($alto == 10) {
				/* Nombre de materia muy largo, multilinea */
				$this->Ln (1);
				$this->SetX (62);
				$this->MultiCell ($w[2], 4, $seccion->materia_desc, 0, 'C', 0, 2);
			} else {
				$this->SetX (62);
				$this->Cell ($w[2], $alto, $seccion->materia_desc, 0, 0, 'C');
			}
			$this->SetY ($save_y);
			$this->SetX (158);
			
			$this->Cell ($w[3], $alto, $seccion->seccion, 1, 0, 'C');
			$materia = $seccion->get_materia ();
			
			$this->Cell ($w[4], $alto, $materia->teoria + $materia->practica, 1, 0, 'C');
			
			$this->Ln ($alto);
		}
		
		$this->Ln (4);
		$this->SetX (36);
		$this->MultiCell (160, 5, 'Se extiende la presente por solicitud del interesado, para los fines que a él convengan.');
		
		$this->Ln (8);
		$this->SetX (36);
		$this->Cell (160, 0, 'A t e n t a m e n t e', 0, 0, 'C');
		
		$this->SetFont ('Georgia', 'b', 10);
		$this->Ln (4);
		$this->SetX (36);
		$this->Cell (160, 0, '“Piensa y Trabaja”', 0, 0, 'C');
		
		$this->Ln (4);
		$this->SetX (36);
		$this->Cell (160, 0, '“Año del Centenario de la Escuela Prepatoria de Jalisco”', 0, 0, 'C');
		
		$this->SetFont ('Georgia', '', 10);
		$this->Ln (4);
		$this->SetX (36);
		$texto = strftime ('Guadalajara, Jalisco, a %d de %B de %Y');
		$this->Cell (160, 0, $texto, 0, 0, 'C');
		
		$this->Ln (12);
		$this->SetX (36);
		
		$this->SetFont ('Georgia', 'b', 10);
		if ($this->departamento->clave == 1500) {
			$this->Cell (160, 5, 'Ing. Patricia Mendoza Sánchez', 0, 0, 'C');
		} else if ($this->departamento->clave == 1510) {
			$this->Cell (160, 5, 'Dra. María Teresa Rodríguez Sahagún', 0, 0, 'C');
		}
		
		$this->SetFont ('Georgia', '', 10);
		$this->Ln (4);
		$this->SetX (36);
		$this->Cell (160, 5, 'Jefe de Departamento', 0, 0, 'C');
		
		$this->Image (dirname(__FILE__).'/data/membretes/membrete_pie_'.$this->departamento->clave.'.jpg', 0, 254, 216, 0);
	}
}
