<?php

class Clientes_Ctrl
{
    public $M_Cliente = null;

    public function __construct()
    {
        $this->M_Cliente = new M_Clientes();
    }

    public function crear($f3)
    {
        $this->M_Cliente->load(['identificacion = ? OR correo = ?', $f3->get('POST.identificacion'), $f3->get('POST.correo')]);
        if ($this->M_Cliente->loaded() > 0) {
            echo json_encode([
                'mensaje' => 'Ya existe un cliente con la identificación o correo que intenta registrar.',
                'info' => [
                    'id' => 0
                ]
            ]);
        } else {
            $this->M_Cliente->set('identificacion', $f3->get('POST.identificacion'));
            $this->M_Cliente->set('nombre', $f3->get('POST.nombre'));
            $this->M_Cliente->set('telefono', $f3->get('POST.telefono'));
            $this->M_Cliente->set('correo', $f3->get('POST.correo'));
            $this->M_Cliente->set('direccion', $f3->get('POST.direccion'));
            $this->M_Cliente->set('uuid', $f3->get('POST.uuid'));
            $this->M_Cliente->save();
            echo json_encode([
                'mensaje' => 'Cliente creado',
                'info' => [
                    'id' => $this->M_Cliente->get('id')
                ]
            ]);
        }
    }

    public function consultar($f3)
    {
        $uuid = $f3->get('PARAMS.uuid');
        $this->M_Cliente->load(['uuid = ?', $uuid]);
        //echo $f3->get('DB')->log();
        $msg = "";
        $item = array();
        if ($this->M_Cliente->loaded() > 0) {
            $msg = "Cliente encontrado.";
            $item = $this->M_Cliente->cast();
        } else {
            $msg = "El Cliente no existe.";
        }
        echo json_encode([
            'mensaje' => $msg,
            'info' => [
                'item' => $item
            ]
        ]);
    }

    public function consultar_id($f3)
    {
        $cliente_id = $f3->get('PARAMS.cliente_id');
        $this->M_Cliente->load(['id= ?', $cliente_id]);
        //echo $f3->get('DB')->log();
        $msg = "";
        $item = array();
        if ($this->M_Cliente->loaded() > 0) {
            $msg = "Cliente encontrado.";
            $item = $this->M_Cliente->cast();
        } else {
            $msg = "El Cliente no existe.";
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
        $result = $this->M_Cliente->find();
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
        $cliente_id = $f3->get('POST.cliente_id');
        $this->M_Cliente->load(['id = ?', $cliente_id]);
        $msg = "";
        if ($this->M_Cliente->loaded() > 0) {
            $msg = "Cliente eliminado.";
            $this->M_Cliente->erase();
        } else {
            $msg = "El Cliente no existe.";
        }
        echo json_encode([
            'mensaje' => $msg,
            'info' => []
        ]);
    }

    public function actualizar($f3)
    {
        $cliente_id = $f3->get('PARAMS.cliente_id');
        $this->M_Cliente->load(['id = ?', $cliente_id]);
        $msg = "";
        $info = array();
        if ($this->M_Cliente->loaded() > 0) {
            $_cliente = new M_Clientes();
            $_cliente->load(['correo = ? AND id <> ?', $f3->get('POST.correo'), $cliente_id]);
            if ($_cliente->loaded() > 0) {
                $msg = "El registro no se pudo modificar debido a que el correo se encuentra uso por otro cliente.";
                $info['id'] = 0;
            } else {
                $this->M_Cliente->set('nombre', $f3->get('POST.nombre'));
                $this->M_Cliente->set('telefono', $f3->get('POST.telefono'));
                $this->M_Cliente->set('direccion', $f3->get('POST.direccion'));
                $this->M_Cliente->set('pais', $f3->get('POST.pais'));
                $this->M_Cliente->set('ciudad', $f3->get('POST.ciudad'));
                $this->M_Cliente->set('correo', $f3->get('POST.correo'));
                $this->M_Cliente->save();
                $msg = "Cliente actualizado.";
                $info['id'] = $this->M_Cliente->get('id');
            }
        } else {
            $msg = "El Cliente no existe.";
            $info['id'] = 0;
        }
        echo json_encode([
            'mensaje' => $msg,
            'info' => $info
        ]);
    }
}
