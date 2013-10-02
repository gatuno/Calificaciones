<?php

Gatuf::loadFunction('Gatuf_HTTP_URL_urlForView');
Gatuf::loadFunction('Gatuf_Shortcuts_RenderToResponse');

class Calif_Views {
	function index ($request, $match) {
		return Gatuf_Shortcuts_RenderToResponse ('calif/index.html', array (), $request);
	}
	
	function login ($request, $match, $success_url = '', $extra_context=array()) {
		if (!empty($request->REQUEST['_redirect_after'])) {
			$success_url = $request->REQUEST['_redirect_after'];
		} else {
			$success_url = Gatuf::config('calif_base').Gatuf::config ('login_success_url', '/');
		}
		
		$error = '';
		if ($request->method == 'POST') {
			foreach (Gatuf::config ('auth_backends', array ('Gatuf_Auth_ModelBackend')) as $backend) {
				$user = call_user_func (array ($backend, 'authenticate'), $request->POST);
				if ($user !== false) {
					break;
				}
			}
			
			if (false === $user) {
				$error = 'The login or the password is not valid. El login y la contraseña son sensibles a las mayúsculas';
			} else {
				if (!$request->session->getTestCookie ()) {
					$error = 'Necesitas habilitar las cookies para acceder a este sitio';
				} else {
					$request->user = $user;
					$request->session->clear ();
					$request->session->setData('login_time', gmdate('Y-m-d H:i:s'));
					$user->last_login = gmdate('Y-m-d H:i:s');
					$user->update ();
					$request->session->deleteTestCookie ();
					return new Gatuf_HTTP_Response_Redirect ($success_url);
				}
				
			}
		}
		/* Mostrar el formulario de login */
		$request->session->createTestCookie ();
		$context = new Gatuf_Template_Context_Request ($request, array ('page_title' => 'Ingresar',
		'_redirect_after' => $success_url,
		'error' => $error));
		$tmpl = new Gatuf_Template ('calif/login_form.html');
		return new Gatuf_HTTP_Response ($tmpl->render ($context));
	}
	
	function logout ($request, $match) {
		$success_url = Gatuf::config ('after_logout_page', '/');
		$user_model = Gatuf::config('gatuf_custom_user','Gatuf_User');
		
		$request->user = new $user_model ();
		$request->session->clear ();
		$request->session->setData ('logout_time', gmdate('Y-m-d H:i:s'));
		if (0 !== strpos ($success_url, 'http')) {
			$murl = new Gatuf_HTTP_URL ();
			$success_url = Gatuf::config('calif_base').$murl->generate($success_url);
		}
		
		return new Gatuf_HTTP_Response_Redirect ($success_url);
	}
	
	public $import_siiau_precond = array ('Gatuf_Precondition::loginRequired');
	function import_siiau ($request, $match) {
		$extra = array ();
		
		if ($request->method == 'POST') {
			$form = new Calif_Form_Views_importsiiau (array_merge ($request->POST, $request->FILES), $extra);
			if ($form->isValid ()) {
				$form->save ();
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Alumno::index');
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Views_importsiiau (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/import_siiau.html',
		                                         array ('page_title' => 'Importar desde Siiau',
		                                         'form' => $form),
		                                         $request);
	}
	
	public $import_oferta_precond = array ('Gatuf_Precondition::loginRequired');
	function import_oferta ($request, $match) {
		$extra = array ();
		
		if ($request->method == 'POST') {
			$form = new Calif_Form_Views_importoferta (array_merge ($request->POST, $request->FILES), $extra);
			if ($form->isValid ()) {
				$form->save ();
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Salon::index');
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Views_importoferta (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/import_oferta.html',
		                                         array ('page_title' => 'Importar Oferta (estilo Monica Durón)',
		                                         'form' => $form),
		                                         $request);
	}
	
	function verificar_oferta ($request, $match) {
		$extra = array ();
		
		if ($request->method == 'POST') {
			$form = new Calif_Form_Views_verificaroferta (array_merge ($request->POST, $request->FILES), $extra);
			if ($form->isValid ()) {
				$observaciones = $form->save ();
				
				$departamento = new Calif_Departamento ();
				$departamento->getDepartamento ($form->cleaned_data['departamento']);
				/* Recuperar cada uno de los salones para observación y
				 * meterlo dentro del arreglo de observaciones */
				$seccion = new Calif_Seccion ();
				$total = count ($observaciones);
				$bien = 0;
				
				foreach ($observaciones as $nrc => &$obs) {
					$seccion->getNrc ($nrc);
					if ($obs['servida']) {
						$bien++;
					}
					$obs['seccion'] = clone ($seccion);
				}
				$mal = $total - $bien;
				
				return Gatuf_Shortcuts_RenderToResponse ('calif/reporte_verificar.html',
		                                         array ('page_title' => 'Reporte verificar oferta',
		                                         'departamento' => $departamento,
		                                         'total' => $total,
		                                         'total_bien' => $bien,
		                                         'total_mal' => $mal,
		                                         'observaciones' => $observaciones),
		                                         $request);
			}
		} else {
			$form = new Calif_Form_Views_verificaroferta (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/verificar_oferta.html',
		                                         array ('page_title' => 'Verificar Oferta',
		                                         'form' => $form),
		                                         $request);
	}
	
	function sobre_oferta ($request, $match) {
		$extra = array ();
		
		if ($request->method == 'POST') {
			$form = new Calif_Form_Views_sobreoferta (array_merge ($request->POST, $request->FILES), $extra);
			
			if ($form->isValid ()) {
				$totales = $form->save ();
				
				$departamento = new Calif_Departamento ();
				$departamento->getDepartamento ($form->cleaned_data['departamento']);
				
				$sql = new Gatuf_SQL ('materia_departamento=%s', $departamento->clave);
				$total_solicitadas =  Gatuf::factory ('Calif_Seccion')->getList (array ('filter' => $sql->gen (), 'count' => true));
				$mega_total = 0;
				
				foreach ($totales as $valor) {
					if ($valor > 0) $mega_total += $valor;
				}
				
				return Gatuf_Shortcuts_RenderToResponse ('calif/reporte_sobreoferta.html',
		                                         array ('page_title' => 'Reporte verificar oferta',
		                                         'departamento' => $departamento,
		                                         'total_solicitadas' => $total_solicitadas,
		                                         'mega_total' => $mega_total,
		                                         'totales' => $totales),
		                                         $request);
			}
		} else {
			$form = new Calif_Form_Views_sobreoferta (null, $extra);
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/verificar_sobreoferta.html',
		                                         array ('page_title' => 'Verificar sobre oferta',
		                                         'form' => $form),
		                                         $request);
	}
	
	function passwordRecoveryAsk ($request, $match) {
		$title = 'Recuperar contraseña';
		
		if ($request->method == 'POST') {
			$form = new Calif_Form_Password ($request->POST);
			if ($form->isValid ()) {
				$url = $form->save ();
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_Password ();
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/user/recuperarcontra-ask.html',
		                                         array ('page_title' => $title,
		                                         'form' => $form),
		                                         $request);
	}
	
	function passwordRecoveryInputCode ($request, $match) {
		$title = 'Recuperar contraseña';
		if ($request->method == 'POST') {
			$form = new Calif_Form_PasswordInputKey($request->POST);
			if ($form->isValid ()) {
				$url = $form->save ();
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
		 	$form = new Calif_Form_PasswordInputKey ();
		}
		
		return Gatuf_Shortcuts_RenderToResponse ('calif/user/recuperarcontra-codigo.html',
		                                         array ('page_title' => $title,
		                                         'form' => $form),
		                                         $request);
	}
	
	function passwordRecovery ($request, $match) {
		$title = 'Recuperar contraseña';
		$key = $match[1];
		
		$email_id = Calif_Form_PasswordInputKey::checkKeyHash($key);
		if (false == $email_id) {
			$url = Gatuf_HTTP_URL_urlForView ('Gatuf_Views::passwordRecoveryInputKey');
			return new Gatuf_HTTP_Response_Redirect ($url);
		}
		$user = new Calif_User ($email_id[1]);
		$extra = array ('key' => $key,
		                'user' => $user);
		if ($request->method == 'POST') {
			$form = new Calif_Form_PasswordReset($request->POST, $extra);
			if ($form->isValid()) {
				$user = $form->save();
				$request->user = $user;
				$request->session->clear();
				$request->session->setData('login_time', gmdate('Y-m-d H:i:s'));
				$user->last_login = gmdate('Y-m-d H:i:s');
				$user->update ();
				/* Establecer un mensaje
				$request->user->setMessage(); */
				$url = Gatuf_HTTP_URL_urlForView ('Calif_Views_Alumno::index');
				return new Gatuf_HTTP_Response_Redirect ($url);
			}
		} else {
			$form = new Calif_Form_PasswordReset (null, $extra);
		}
		return Gatuf_Shortcuts_RenderToResponse ('calif/user/recuperarcontra.html',
		                                         array ('page_title' => $title,
		                                         'new_user' => $user,
		                                         'form' => $form),
		                                         $request);
	}
}
