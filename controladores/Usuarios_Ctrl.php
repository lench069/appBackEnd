<?php
include "php1/class.phpmailer.php";
include "php1/class.smtp.php";

class Usuarios_Ctrl
{
    public $M_Usuario = null;

    public function __construct()
    {
        $this->M_Usuario = new M_Usuarios();
    }

    public function crear($f3)
    {
        $_usuario = new M_Usuarios();
        $_usuario->load(['usuario = ? OR correo = ?', $f3->get('POST.usuario'), $f3->get('POST.correo')]);
        $msg = "";
        $id = 0;
        if ($_usuario->loaded() > 0) {
            $msg = "El nombre de usuario o correo que intenta usar se encuentran registrados.";
        } else {
            $this->M_Usuario->set('usuario', $f3->get('POST.usuario'));
            //$this->M_Usuario->set('clave', md5($f3->get('POST.clave')));   MD5
            $this->M_Usuario->set('clave', ($f3->get('POST.clave')));
            $this->M_Usuario->set('nombre', $f3->get('POST.nombre'));
            $this->M_Usuario->set('telefono', $f3->get('POST.telefono'));
            $this->M_Usuario->set('correo', $f3->get('POST.correo'));
            $this->M_Usuario->set('activo', $f3->get('POST.activo'));
            $this->M_Usuario->save();
            $id = $this->M_Usuario->get('id');
            $msg = "Usuario creado";
        }
        echo json_encode([
            'mensaje' => $msg,
            'info' => [
                'id' => $id
            ]
        ]);
    }

    public function consultar($f3)
    {
        $usuario_id = $f3->get('PARAMS.usuario_id');
        $this->M_Usuario->load(['id = ?', $usuario_id]);
        $msg = "";
        $item = array();
        if ($this->M_Usuario->loaded() > 0) {
            $msg = "Usuario encontrado.";
            $item = $this->M_Usuario->cast();
        } else {
            $msg = "El Usuario no existe.";
        }
        echo json_encode([
            'mensaje' => $msg,
            'info' => [
                'item' => $item
            ]
        ]);
    }

    public function listado($f3)
    {
        $result = $this->M_Usuario->find(['nombre LIKE ?', '%' . $f3->get('POST.texto') . '%']);
        $items = array();
        foreach ($result as $producto) {
            $items[] = $producto->cast();
        }
        echo json_encode([
            'mensaje' => count($items) > 0 ? '' : 'Aún no hay registros para mostrar.',
            'info' => [
                'items' => $items,
                'total' => count($items)
            ]
        ]);
    }

    public function eliminar($f3)
    {
        $usuario_id = $f3->get('POST.usuario_id');
        $this->M_Usuario->load(['id = ?', $usuario_id]);
        $msg = "";
        if ($this->M_Usuario->loaded() > 0) {
            $msg = "Usuario eliminado.";
            $this->M_Usuario->erase();
        } else {
            $msg = "El Usuario no existe.";
        }
        echo json_encode([
            'mensaje' => $msg,
            'info' => []
        ]);
    }

    public function actualizar($f3)
    {
        $usuario_id = $f3->get('PARAMS.usuario_id');
        $this->M_Usuario->load(['id = ?', $usuario_id]);
        $msg = "";
        $info = array();
        if ($this->M_Usuario->loaded() > 0) {
            $_usuario = new M_Usuarios();
            $_usuario->load(['(usuario = ? OR correo = ?) AND id <> ?', $f3->get('POST.usuario'), $f3->get('POST.correo'), $usuario_id]);
            if ($_usuario->loaded() > 0) {
                $msg = "El registro no se pudo modificar debido a que el nombre usuario o correo se encuentra uso por otro usuario.";
                $info['id'] = 0;
            } else {
                $this->M_Usuario->set('usuario', $f3->get('POST.usuario'));
                if(/*md5*/($f3->get('POST.clave')) != $this->M_Usuario->get('clave') && $f3->get('POST.clave') != '') {
                    $this->M_Usuario->set('clave', $f3->get('POST.clave'));
                }
                $this->M_Usuario->set('nombre', $f3->get('POST.nombre'));
                $this->M_Usuario->set('telefono', $f3->get('POST.telefono'));
                $this->M_Usuario->set('correo', $f3->get('POST.correo'));
                $this->M_Usuario->set('activo', $f3->get('POST.activo'));
                $this->M_Usuario->save();
                $msg = "Usuario actualizado.";
                $info['id'] = $this->M_Usuario->get('id');
            }
        } else {
            $msg = "El usuario no existe.";
            $info['id'] = 0;
        }
        echo json_encode([
            'mensaje' => $msg,
            'info' => $info
        ]);
    }

    public function login($f3)
    {
        $this->M_Usuario->load(['usuario = ?',$f3->get('POST.usuario')]);
       
        $msg='';
        $item = array();
        if($this->M_Usuario->loaded() > 0)
        {
            $this->M_Usuario->load(['clave = ? AND usuario = ?',$f3->get('POST.clave'), $f3->get('POST.usuario')]);
            
            if($this->M_Usuario->loaded() > 0)
            {
                $msg = 'true';
                $item = $this->M_Usuario->cast();
            }else{
                $msg = 'clave incorrecta';
            }
   
        }else
        {
            $msg = 'usuario no existe';

        }
        echo json_encode([
            'mensaje' => $msg,
            'info' =>[
                'item'=>$item
            ]
        ]);
        
    }

    public function olvide_contrasenia($f3)
{  
    $this->M_Usuario->load(['correo = ?',$f3->get('POST.correo')]);
    $pass = $this->generateRandomString(6);
    $msg='';
    $flag='';
    $item = array();
    if($this->M_Usuario->loaded() > 0)
    {
        $this->M_Usuario->set('clave', $pass );
        $this->M_Usuario->save();
        if($this->M_Usuario->save())
        {
            $this->enviar_correo_datos($f3->get('POST.correo'),'Olvide mi contraseña',$pass);
        }
        $item = $this->M_Usuario->cast();
        $flag = 'true';
        $msg = 'Solicitud de cambio de contraseña correcta';

    }else
    {
        $flag = 'false';
        $msg = 'No existe usuario con ese CI/RUC o correo';
    }
    echo json_encode([
        'mensaje' => $msg,
        'flag' => $flag,
        'info' =>[
            'item'=>$item
        ]
    ]);
}

function generateRandomString($length) { 
    return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length); 
}

public function enviar_correo_datos($correo,$asuntop,$pass)
    {
        $nombre = 'Recuperacion de contraseña';
        $mail = 'info_repicar@riobytes.com';
        $asunto = $asuntop;

        $email_user = "info_repicar@riobytes.com";
        $email_password = "P@ssw0rd6969";
        $the_subject = $asunto;
        $address_to = $correo; //AQUI CAMBIAR EL CORREO AL QUE QUIEES QUE TE LLEGUEN LOS CORREOS
        $from_name = $nombre;
        $phpmailer = new PHPMailer();
        // ---------- datos de la cuenta de Gmail -------------------------------
        $phpmailer->Username = $email_user;
        $phpmailer->Password = $email_password; 
        //-----------------------------------------------------------------------
        // $phpmailer->SMTPDebug = 1;
        $phpmailer->SMTPSecure = 'ssl';
        $phpmailer->Host = "mail.riobytes.com"; // GMail
        $phpmailer->Port = 465;
        $phpmailer->IsSMTP(); // use SMTP
        $phpmailer->SMTPAuth = true;
        $phpmailer->setFrom($phpmailer->Username,$from_name);
        $phpmailer->AddAddress($address_to); // recipients email
        $phpmailer->Subject = $the_subject;	 
        $phpmailer->Body .="<body style='background-color: black'>

        <!--Copia desde aquí-->
        <table style='max-width: 600px; padding: 10px; margin:0 auto; border-collapse: collapse;'>
            <tr>
                <td style='background-color: #093856; text-align: left; padding: 0'>
                    
                        <center><img width='15%' style='display:block; margin: 1.5% 3%' src='https://docs.google.com/uc?export=download&id=1iNXe-TAFQKqvkjbsHB1wmpHSHPJD2VaO'></center>
                    
                </td>
            </tr>

            <tr>
                <td style='padding: 0'
                    <img style='padding: 0; display: block' src='https://s19.postimg.org/y5abc5ryr/alola_region.jpg' width='100%'>
                </td>
            </tr>
            
            <tr>
                <td style='background-color: #093856'>
                    <div style='color: #FDFEFE; margin: 4% 10% 2%; text-align: justify;font-family: sans-serif'>
                        <h2 style='color: #FDC134; margin: 0 0 7px'>Proceso de recupuracion de clave se realizo exitosamente.!</h2>
                        <p style='margin: 2px; font-size: 15px; style='color: #FFFF'>
                        Su contraseña temporal es la siguiente:  </p>
                        <ul style='font-size: 15px;  margin: 10px 0 ; style='color: #FFFF'>
                            <li>$pass</li>                             
                        </ul>
                        <p style='margin: 2px; font-size: 15px; style='color: #FFFF' >
                        Por favor al ingresar cambiar su contraña.
                        </p>
                        
                        <p style='color: #b3b3b3; font-size: 12px; text-align: center;margin: 30px 0 0'>Derechos reservados</p>
                    </div>
                </td>
            </tr>
        </table>
        <!--hasta aquí-->

        </body>";


        $phpmailer->IsHTML(true);
        $phpmailer->Send();

    }

}
