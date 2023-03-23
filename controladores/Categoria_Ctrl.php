<?php

class Categoria_Ctrl
{
    public $M_Categoria = null;
    public $server = 'http://192.168.100.94/appBackEnd/';
    //public $server = 'http://riobytes.com/appBackEnd/';
    public function __construct()
    {
        $this->M_Categoria = new M_Categoria();
    }

   /* public function listado($f3)
    {
        $result = $this->M_Categoria->find();
        $items = array();
        foreach ($result as $categoria) {
            $items[] = $categoria->cast();
        }
        echo json_encode([
            'mensaje' => count($items) > 0 ? '' : 'Aún no hay registros para mostrar.',
            'info' => [
                'items' => $items,
                'total' => count($items)
            ]
        ]);
    }*/

    public function consultar($f3)
    {
        $categoria_id = $f3->get('PARAMS.categoria_id');
        $this->M_Categoria->load(['id = ?', $categoria_id]);
        
        $msg = "";
        $item = array();
        if ($this->M_Categoria->loaded() > 0) {
            $msg = "Categoria encontrada.";
            $item = $this->M_Categoria->cast();
            $item['imagen'] = !empty($item['imagen']) ? $this->server. $item['imagen'] : 'http://via.placeholder.com/300x300';
            $items[] = $item;
        } else {
            $msg = "El categoria no existe.";
        }
        echo json_encode([
            'mensaje' => $msg,
            'info' => [
                'item' => $item
            ]
        ]);
    }

    public function crear($f3)
    {
        $_categoria = new M_Categoria();
        $_categoria->load(['id = ?', $f3->get('POST.id')]);
        //echo $f3->get('DB')->log();
        $msg = "";
        if ($_categoria->loaded() > 0) {
            $msg = "El id que intenta usar está usado por otro producto.";
        } else {
            $this->M_Categoria->set('titulo', $f3->get('POST.nombre'));
            $this->M_Categoria->set('descripcion', $f3->get('POST.descripcion'));
            $this->M_Categoria->set('imagen', $this->Guardar_Imagen($f3->get('POST.imagen')));
            $this->M_Categoria->save();          
            $msg = "categoria creada.";
        }
        echo json_encode([
            'mensaje' => $msg,
            'info' => [
                'id' => $id
            ]
        ]);
    }


    public function Guardar_Imagen($contenido) 
    {
        $nombre_imagen = '';
        if (!empty($contenido)){

            if (strpos($contenido, 'appBackEnd')) {
                $nombre_imagen = substr($contenido, -23);   
            }else{
                
                $contenido = explode('base64,', $contenido);
                $imagen = $contenido[1];
                $nombre_imagen = 'imagenes/' . time() . '.jpg';
                file_put_contents($nombre_imagen, base64_decode($imagen));  
            }   
        }
       
        return $nombre_imagen;
    }

    public function eliminar($f3)
    {
        $categoria_id = $f3->get('POST.categoria_id');
        $this->M_Categoria->load(['id = ?', $categoria_id]);
        $msg = "";
        if ($this->M_Categoria->loaded() > 0) {
            $msg = "Categoria eliminado.";
            $this->M_Categoria->erase();
        } else {
            $msg = "La categoria no existe.";
        }
        echo json_encode([
            'mensaje' => $msg,
            'info' => []
        ]);
    }

    public function listado($f3)
    {
        $result = $this->M_Categoria->find(['titulo LIKE ?', '%' . $f3->get('POST.texto') . '%']);
        $items = array();
        foreach ($result as $cateforia) {
            $item = $cateforia->cast();
            $item['imagen'] = !empty($item['imagen']) ? $this->server . $item['imagen'] : 'http://via.placeholder.com/300x300';
            $items[] = $item;
        }
        echo json_encode([
            'mensaje' => count($items) > 0 ? '' : 'Aún no hay registros para mostrar.',
            'info' => [
                'items' => $items,
                'total' => count($items)
            ]
        ]);
    }

    public function actualizar($f3)
    {
        $producto_id = $f3->get('PARAMS.categoria_id');
        $this->M_Categoria->load(['id = ?', $producto_id]);
        //echo $f3->get('DB')->log();
        $msg = "";
        $info = array();
        if ($this->M_Categoria->loaded() > 0) {

                $this->M_Categoria->set('titulo', $f3->get('POST.nombre'));
                $this->M_Categoria->set('imagen', $this->Guardar_Imagen($f3->get('POST.imagen')));
                $this->M_Categoria->set('descripcion', $f3->get('POST.descripcion'));
                $this->M_Categoria->save();
                //echo $f3->get('DB')->log();
                $msg = "Categoria actualizada.";
                $info['id'] = $this->M_Categoria->get('id');
            
        } else {
            $msg = "El Categoria no existe.";
            $info['id'] = 0;
        }
        echo json_encode([
            'mensaje' => $msg,
            'info' => $info
        ]);
    }

}
