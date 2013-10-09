<?php

class Calif_PDF_Horario extends External_FPDF {
	public $maestro;
	public $horarios;
	public $secciones;
	public $departamento;
	public $total_horarios;
	
	function Header () {
		$this->SetY (7);
		$this->SetX (17);
		$this->SetFont('Times', '', 12);
		$this->Cell (0, 0, 'Horario del profesor');
		
		$this->SetX (-45);
		$this->Cell (0, 0, 'Página '.$this->PageNo().' de {nb}');
	}
	
	function Footer () {
		$this->SetY (-7);
		$this->SetX (17);
		$this->SetFont('Times', '', 12);
		$this->Cell (0, 0, 'http://siiauescolar.siiau.udg.mx/wse/siphora.horario');
		
		$this->SetX (-45);
		setlocale (LC_TIME, 'es_MX.UTF-8');
		$this->Cell (0, 0, strftime ('%d/%m/%Y'));
	}
	
	function renderBase () {
		$this->AliasNbPages();
		$this->SetMargins (19, 20);
		
		$this->SetFont('Times', '', 12);
		$this->AddPage();
		
		$this->Image(dirname (__FILE__).'/data/logo_udg_mini.gif',23, 23, 0, 0);
		$this->SetY (0);
		$this->Ln (44);
		$this->AddFont ('Arial', '');
		$this->SetFont('Arial', 'B', 18);
		$this->Cell (0, 0, 'Universidad de Guadalajara', 0, 0, 'C');
		
		$this->SetY (0);
		$this->Ln (49);
		$this->MultiCell (0, 7, 'CENTRO UNIVERSITARIO DE CIENCIAS EXACTAS E INGENIERIAS', 0, 'C');
		
		$this->SetY (0);
		$this->Ln (98);
		$this->SetFont ('Times', 'BU', 18);
		$this->Cell (0, 0, 'Horario de clases', 0, 0, 'C');
		
		$this->SetY (0);
		$this->Ln (119);
		$this->SetFont ('times', 'B', 14);
		$cadena = sprintf ('Profesor: %s %s (%s)', mb_strtoupper ($this->maestro->apellido), mb_strtoupper ($this->maestro->nombre), $this->maestro->codigo);
		$this->Cell (0, 0, $cadena, 0, 0, 'L');
		
		$this->SetY (0);
		$this->Ln (127);
		$this->SetFont ('times', '', 12);
		$cadena = 'Por este conducto le hago llegar su horario de la(s) asignatura(s) que impartirá en este Departamento en el ciclo escolar 2013-B, con el objetivo de que realice la programación de su(s) curso(s) en las fechas establecidas y de acuerdo a su petición.';
		$this->MultiCell (0, 5, $cadena, 0, 'L');
	}
	
	function renderHorario () {
		$w = array (10, 14, 44, 9, 6, 12, 12, 9, 9, 4, 4, 4, 4, 4, 4, 14, 14);
		$headers = array ('NRC', 'CLAVE', 'MATERIA', 'SEC', 'CR', 'EDIF', 'AULA', 'INI', 'TER', 'L', 'M', 'I', 'J', 'V', 'S', 'DEL', 'AL');
		
		$dias = array ('l', 'm', 'i', 'j', 'v', 's');
		$this->SetFont ('courier', 'B', 12);
		$this->SetY(0);
		$this->Ln(151);
		for ($g = 0; $g < count ($headers); $g++) {
			$this->Cell ($w[$g], 6, $headers[$g], 1, 0, 'C');
		}
		
		$this->Ln();
		$this->AddFont ('Verdana');
		$this->SetFont ('verdana', '', 8);
		foreach ($this->secciones as $seccion) {
			$first = true;
			foreach ($this->horarios[$seccion->nrc] as $hora) {
				if ($first) {
					$this->Cell ($w[0], 8, $seccion->nrc, 1, 0, 'C');
					$this->Cell ($w[1], 8, $seccion->materia, 1, 0, 'L');
					$save_y = $this->GetY();
					$save_x = $this->GetX();
					$this->Cell ($w[2], 8, '', 1, 0, '');
					$this->SetY($save_y);
					$this->SetX($save_x);
					$this->MultiCell ($w[2], 4, $seccion->materia_desc, 0, 'L', 0, 2);
					$this->SetY ($save_y);
					$this->SetX (87);
					$this->Cell ($w[3], 8, $seccion->seccion, 1, 0, 'L');
					$first = false;
				} else {
					$this->Cell ($w[0], 8, '', 'BL', 0, 'C');
					$this->Cell ($w[1], 8, '', 'B', 0, 'L');
					$this->Cell ($w[2], 8, '', 'B', 0, 'L');
					$this->Cell ($w[3], 8, '', 'B', 0, 'L');
				}
				$this->Cell ($w[4], 8, '', 1, 0, 'L'); /* FIXME: Cŕeditos aquí */
				$this->Cell ($w[5], 8, $hora->salon_edificio, 1, 0, 'C');
				$this->Cell ($w[6], 8, $hora->salon_aula == 'A050' ? '' : $hora->salon_aula, 1, 0, 'C');
				$this->Cell ($w[7], 8, date('H:i', strtotime($hora->inicio)), 1, 0, 'C');
				$this->Cell ($w[8], 8, date('H:i', strtotime($hora->fin)), 1, 0, 'C');
				
				$g = 9;
				foreach ($dias as $value) {
					if ($hora->$value) {
						$this->Cell ($w[$g], 8, mb_strtoupper ($value), 1, 0, 'C');
					} else {
						$this->Cell ($w[$g], 8, '', 1, 0, 'C');
					}
					$g++;
				}
				
				$altura_y = $this->GetY ();
				$x_del = $this->GetX ();
				$this->Cell ($w[15], 8, '', 1, 0, 'L');
				$x_al = $this->GetX ();
				$this->Cell ($w[16], 8, '', 1, 0, 'L');
				
				$this->SetXY ($x_del, $altura_y);
				$this->MultiCell ($w[15], 4, "19-\nAUG-13", 0, 'L');
				$this->SetXY ($x_al, $altura_y);
				$this->MultiCell ($w[15], 4, "14-\nDEC-13", 0, 'L');
				
				$this->SetXY ($x_del, $altura_y);
				$this->Cell ($w[15], 8, '', 1, 0, 'L');
				$this->Cell ($w[16], 8, '', 1, 0, 'L');
				$this->Ln ();
			}
		}
	}
	
	function renderFirmas () {
		$this->Ln ();
		if ($this->total_horarios != 6) $this->Ln ();
		if ($this->total_horarios < 4 || $this->total_horarios > 6) {
			$this->Ln ();
		}
		if ($this->total_horarios == 9 || $this->total_horarios == 7) $this->Ln ();
		if ($this->total_horarios == 8 || $this->total_horarios == 7) {
			$this->Ln ();
			$this->Ln ();
		}
		$this->SetFont('Times', '', 12);
		//throw new Exception ("Página = ".$this->page.", Alto, Y = ".$this->y);
		$this->Cell (0, 2, 'ATENTAMENTE', 0, 1, 'C');
		//throw new Exception ("Página = ".$this->page.",Alto, Y = ".$this->y. ", Top margin = ".$this->tMargin);
		$this->Cell (0, 5, '"PIENSA Y TRABAJA"', 0, 1, 'C');
		setlocale (LC_TIME, 'es_MX.UTF-8');
		$cadena = strftime ('GUADALAJARA, JAL., %d de %B de %Y');
		$this->Cell (0, 8, $cadena, 0, 1, 'C');
		
		$mitad = ($this->w) / 2;
		
		$this->Ln ();
		$this->Ln ();
		if ($this->total_horarios < 4 || $this->total_horarios > 6) {
			$this->Ln ();
		}
		
		$this->x = 0;
		$this->Cell ($mitad, 5, '__________________________________', 0, 0, 'C');
		$this->Cell ($mitad, 5, '__________________________________', 0, 0, 'C');
		$this->Ln ();
		$this->x = 0;
		if (!is_null ($this->departamento)) {
			if ($this->departamento->clave == 1500) {
				$this->Cell ($mitad, 5, 'Ing. Patricia Mendoza Sánchez', 0, 0, 'C');
			} else if ($this->departamento->clave == 1510) {
				$this->Cell ($mitad, 5, 'Dra. María Teresa Rodríguez Sahagún', 0, 0, 'C');
			}
		} else {
			$this->Cell ($mitad, 5, 'Jefe del departamento', 0, 0, 'C');
		}
 		$this->Cell ($mitad, 5, 'Recibe', 0, 0, 'C');
 		
		if (!is_null ($this->departamento)) {
		    if ($this->departamento->clave == 1500 || $this->departamento->clave == 1510) {
				$this->Ln ();
				$this->x = 0;
				$this->Cell ($mitad, 5, 'Jefa del departamento', 0, 0, 'C');
			}
		}
	}
}
