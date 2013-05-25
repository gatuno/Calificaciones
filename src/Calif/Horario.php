<?php

class Calif_Horario extends Gatuf_Model {
	public $nrc;
	public $hora_inicio;
	public $hora_fin;
	public $salon;
	
	public $lunes, $martes, $miercoles, $jueves, $viernes, $sabado;
	
	function __construct () {
		$this->_getConnection();
		
		$this->tabla = 'Horarios';
	}
	
	public function restore () {
		$this->lunes = Gatuf_DB_BooleanFromDB ($this->lunes);
		$this->martes = Gatuf_DB_BooleanFromDB ($this->martes);
		$this->miercoles = Gatuf_DB_BooleanFromDB ($this->miercoles);
		$this->jueves = Gatuf_DB_BooleanFromDB ($this->jueves);
		$this->viernes = Gatuf_DB_BooleanFromDB ($this->viernes);
		$this->sabado = Gatuf_DB_BooleanFromDB ($this->sabado);
		$this->hora_inicio = Gatuf_DB_HoraSiiauFromDB ($this->hora_inicio);
		$this->hora_fin = Gatuf_DB_HoraSiiauFromDB ($this->hora_fin);
	}
	
	function create () {
		$req = sprintf ('INSERT INTO %s (nrc, hora_inicio, hora_fin, salon, lunes, martes, miercoles, jueves, viernes, sabado) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s);', $this->getSqlTable(), Gatuf_DB_IntegerToDb ($this->nrc, $this->_con), Gatuf_DB_HoraSiiauToDb ($this->hora_inicio, $this->_con), Gatuf_DB_HoraSiiauToDb ($this->hora_fin, $this->_con), Gatuf_DB_IntegerToDb ($this->salon, $this->_con), Gatuf_DB_BooleanToDB ($this->lunes, $this->_con), Gatuf_DB_BooleanToDB ($this->martes, $this->_con), Gatuf_DB_BooleanToDB ($this->miercoles, $this->_con), Gatuf_DB_BooleanToDB ($this->jueves, $this->_con), Gatuf_DB_BooleanToDB ($this->viernes, $this->_con), Gatuf_DB_BooleanToDB ($this->sabado, $this->_con));
		
		$this->_con->execute ($req);
		
		return true;
	}
}
