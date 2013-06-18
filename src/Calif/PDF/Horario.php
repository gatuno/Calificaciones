<?php

class Calif_PDF_Horario extends External_FPDF {
	public $maestro;
	public $horarios;
	public $secciones;
	
	function Header () {
		$this->SetY (7);
		$this->SetX (17);
		$this->Cell (0, 0, 'Horario del profesor');
		
		$this->SetX (-45);
		$this->Cell (0, 0, 'Página '.$this->PageNo().' de {nb}');
	}
	
	function Footer () {
		$this->SetY (-9);
		$this->SetX (17);
		$this->Cell (0, 0, 'http://siiauescolar.siiau.udg.mx/wse/siphora.horario');
		
		$this->SetX (-45);
		$this->Cell (0, 0, '17/06/2013');
	}
	
	function renderBase () {
		$this->AliasNbPages();
		$this->SetFont('Times', '', 12);
		$this->AddPage();
		
		$this->Image(dirname (__FILE__).'/data/logo_udg_mini.gif',23, 23, 0, 0);
		$this->SetY (0);
		$this->Ln (42);
		$this->Cell (0, 0, 'Universidad de Guadalajara', 0, 0, 'C');
		
		$this->SetY (0);
		$this->Ln (49);
		$this->MultiCell (0, 0, 'Centro Universitario de Ciencias Exactas e Ingenierias', 0, 'C');
		
		$this->SetY (0);
		$this->Ln (94);
		$this->Cell (0, 0, 'Horario de clases', 0, 0, 'C');
		
		$this->SetY (0);
		$this->Ln (116);
		$cadena = sprintf ('Profesor: %s %s (%s)', $this->maestro->apellido, $this->maestro->nombre, $this->maestro->codigo);
		$this->Cell (0, 0, $cadena, 0, 0, 'L');
		
		$this->SetY (0);
		$this->Ln (127);
		$cadena = 'Por este conducto le hago llegar a su horario de la(s) asignatura(s) que impartirá en este Departamento en el ciclo escolar 2013-B, con el objetivo de que realice la programación de su(s) curso(s) en las fechas establecidas y de acuerdo a su petición.';
		$this->MultiCell (0, 5, $cadena, 0, 'L');
	}
	
	function renderHorario () {
		$w = array (10, 14, 44, 9, 6, 12, 12, 9, 9, 4, 4, 4, 4, 4, 4, 13, 13);
		$headers = array ('NRC', 'CLAVE', 'MATERIA', 'SEC', 'CR', 'EDIF', 'AULA', 'INI', 'TER', 'L', 'M', 'I', 'J', 'V', 'S', 'DEL', 'AL');
		
		$dias = array ('lunes' => 'L', 'martes' => 'M', 'miercoles' => 'I', 'jueves' => 'J', 'viernes' => 'V', 'sabado' => 'S');
		$this->SetY(0);
		$this->Ln(151);
		for ($g = 0; $g < count ($headers); $g++) {
			$this->Cell ($w[$g], 6, $headers[$g], 1, 0, 'C');
		}
		
		$this->Ln();
		
		foreach ($this->secciones as $seccion) {
			foreach ($this->horarios[$seccion->nrc] as $hora) {
				$this->Cell ($w[0], 8, $seccion->nrc, 1, 0, 'C');
				$this->Cell ($w[1], 8, $seccion->materia, 1, 0, 'L');
				$this->Cell ($w[2], 8, $seccion->materia_desc, 1, 0, 'L');
				$this->Cell ($w[3], 8, $seccion->seccion, 1, 0, 'L');
				$this->Cell ($w[4], 8, '', 1, 0, 'L');
				$this->Cell ($w[5], 8, $hora->salon_edificio, 1, 0, 'C');
				$this->Cell ($w[6], 8, $hora->salon_aula, 1, 0, 'C');
				$this->Cell ($w[7], 8, $hora->hora_inicio, 1, 0, 'C');
				$this->Cell ($w[8], 8, $hora->hora_fin, 1, 0, 'C');
				
				$g = 9;
				foreach ($dias as $key => $value) {
					if ($hora->$key) {
						$this->Cell ($w[$g], 8, $value, 1, 0, 'C');
					} else {
						$this->Cell ($w[$g], 8, '', 1, 0, 'C');
					}
					$g++;
				}
				
				$this->Cell ($w[15], 8, '19-AUG-13', 1, 0, 'L');
				$this->Cell ($w[16], 8, '14-DEC-13', 1, 0, 'L');
				$this->Ln();
			}
		}
	}
}
