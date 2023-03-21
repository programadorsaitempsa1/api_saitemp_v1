<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\EstadoUsuarioController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\SigContratoController;
use App\Http\Controllers\DepartamentoController;
use App\Http\Controllers\SigEstadoEmpleadoController;
use App\Http\Controllers\SigTipoDocumentoIdentidadController;
use App\Http\Controllers\SigCargoController;
use App\Http\Controllers\SigOrdenTrabajoController;
use App\Http\Controllers\SigEstadoOrdenTrabajoController;
use App\Http\Controllers\SigFormularioController;
use App\Http\Controllers\AuthUsuarioController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\SigEstadoContratoController;
use App\Http\Controllers\MenuRolController;
use App\Http\Controllers\SigPermisoRolController;
use App\Http\Controllers\MunicipioController;
use App\Http\Controllers\SigZonaController;
use App\Http\Controllers\SigEmpleadoController;
use App\Http\Controllers\SigContratoEmpleadoController;
use App\Http\Controllers\SigOrdenTrabajoEmpleadoController;
use App\Http\Controllers\SigFormularioOrdenTrabajoController;
use App\Http\Controllers\CostoController;
use App\Http\Controllers\UnidadMedidaController;
use App\Http\Controllers\CodigoItemController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PaisController;
use App\Http\Controllers\GeneroController;
use App\Http\Controllers\EstadoCivilController;
use App\Http\Controllers\FormaPagoController;
use App\Http\Controllers\BancoController;
use App\Http\Controllers\TipoContratoController;
use App\Http\Controllers\EstadoLaboralEmpleadoController;
use App\Http\Controllers\ConvenioController;
use App\Http\Controllers\EmpleadoController;
use App\Http\Controllers\SucursalSSController;
use App\Http\Controllers\CompaniaController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\CentroCostosController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\CentroTrabajoController;
use App\Http\Controllers\CuentaGastosLController;
use App\Http\Controllers\CargoController;
use App\Http\Controllers\ModoLiquidacionController;
use App\Http\Controllers\ClaseSalarioController;
use App\Http\Controllers\FondoSPController;
use App\Http\Controllers\TipoCotizanteController;
use App\Http\Controllers\SubTipoCotizanteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// TODO: Colocar los name a las rutas 
Route::group([
    'middleware' => 'api',
    'prefix' => 'v1'

], function ($router) {

    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/register2', [AuthUsuarioController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']); 
    
    // Usuarios
    Route::get('/users/{cantidad}', [UsuarioController::class, 'index']); 
    Route::get('/userlogued', [UsuarioController::class, 'userlogued']); 
    Route::get('/userbyid/{id}', [UsuarioController::class, 'userById']); 
    Route::delete('/user/{id}', [UsuarioController::class, 'destroy']); 
    // Route::post('/user', [UsuarioController::class, 'create']); 
    Route::post('/user', [UsuarioController::class, 'update']); 
    Route::get('/usuariosporcontrato', [UsuarioController::class, 'usuariosporcontrato']); 
    Route::get('/usuariosporcontrato/{id}', [UsuarioController::class, 'usuariosporcontrato2']); 
    
     // Opciones de menú
     Route::get('/menus', [MenuController::class, 'index']);    
     Route::post('/menus', [MenuController::class, 'create']);    
     Route::post('/menus/{id}', [MenuController::class, 'update']);    
     Route::delete('/menus/{id}', [MenuController::class, 'destroy']);    
     Route::get('/menus/{id}', [MenuController::class, 'menubyRole']);    

      // Rol menú
    Route::get('/rolmenu/{cantidad}', [MenuRolController::class, 'rolesMenus']);    
    Route::post('/rolmenu', [MenuRolController::class, 'create']);    
    Route::post('/rolmenu/{id}', [MenuRolController::class, 'update']);    
    Route::delete('/rolmenu/{id}', [MenuRolController::class, 'destroy']); 
    Route::get('/rolmenuporid/{id}', [MenuRolController::class, 'rolesMenusbyid']); 
    Route::get('/rolesConMenu', [MenuRolController::class, 'rolesConMenu']); 
    Route::post('/rolmenuborradomasivo', [MenuRolController::class, 'borradomasivo']);
    Route::post('/rolmenuactualizacionmasiva', [MenuRolController::class, 'actualizacionmasiva']);
  
    // Estados de usuario
    Route::get('/estadousuarios', [EstadoUsuarioController::class, 'index'])->middleware('auth');    
    Route::post('/estadousuarios', [EstadoUsuarioController::class, 'create']);    
    Route::post('/estadousuarios/{id}', [EstadoUsuarioController::class, 'update']);    
    Route::delete('/estadousuarios/{id}', [EstadoUsuarioController::class, 'destroy']);

    // Géneros
    Route::get('/genero', [GeneroController::class, 'index']);  
    
    // Estados civiles
    Route::get('/estadocivil', [EstadoCivilController::class, 'index']);  
    
    // Estados civiles
    Route::get('/formapago', [FormaPagoController::class, 'index']); 
    
    // Estados civiles
    Route::get('/banco', [BancoController::class, 'index']); 
    
    // Estados civiles
    Route::get('/tipocontrato', [TipoContratoController::class, 'index']); 
    
    // Estados civiles
    Route::get('/estadolaboralempleado', [EstadoLaboralEmpleadoController::class, 'index']); 
    
    // Estados civiles
    Route::get('/convenio', [ConvenioController::class, 'index']); 
    
    // Estados civiles
    Route::get('/empleado', [EmpleadoController::class, 'index']); 
    
    // Estados civiles
    Route::get('/sucursalss', [SucursalSSController::class, 'index']); 
    
    // Estados civiles
    Route::get('/compania', [CompaniaController::class, 'index']); 
    
    // Estados civiles
    Route::get('/sucursal', [SucursalController::class, 'index']); 
    
    // Estados civiles
    Route::get('/centrocostos', [CentroCostosController::class, 'index']); 
    
    // Estados civiles
    Route::get('/area', [AreaController::class, 'index']); 
    
    // Estados civiles
    Route::get('/centrotrabajo', [CentroTrabajoController::class, 'index']); 
    
    // Estados civiles
    Route::get('/cuentagastosl', [CuentaGastosLController::class, 'index']); 
    
    // Estados civiles
    Route::get('/cargo', [CargoController::class, 'index']); 
    
    // Estados civiles
    Route::get('/modoliquidacion', [ModoLiquidacionController::class, 'index']); 
    
    // Estados civiles
    Route::get('/clasesalario', [ClaseSalarioController::class, 'index']);
    
    // Estados civiles
    Route::get('/tipocotizante', [TipoCotizanteController::class, 'index']);
    
    // Estados civiles
    Route::get('/subtipocotizante', [SubTipoCotizanteController::class, 'index']);

    // Estados civiles
    Route::get('/fondosalud', [FondoSPController::class, 'fondosalud']);
    Route::get('/fondopension', [FondoSPController::class, 'fondopension']);
    Route::get('/cajaCompensacion', [FondoSPController::class, 'cajaCompensacion']);
    Route::get('/riesgolaboral', [FondoSPController::class, 'riesgoLaboral']);
    Route::get('/fondocesantias', [FondoSPController::class, 'fondoCesantias']);

    // Roles de usuario
    Route::get('/roles/{cantidad}', [RolController::class, 'index']);    
    Route::post('/roles', [RolController::class, 'create']);    
    Route::post('/roles/{id}', [RolController::class, 'update']);    
    Route::delete('/roles/{id}', [RolController::class, 'destroy']); 
    Route::post('/rolesborradomasivo', [RolController::class, 'borradomasivo']);
    Route::post('/rolesactualizacionmasiva', [RolController::class, 'actualizacionmasiva']); 
    Route::get('/roleslista', [RolController::class, 'lista']);    
    Route::post('/unidadmedidaborradomasivo', [UnidadMedidaController::class, 'borradomasivo']);
    Route::post('/unidadmedidaactualizacionmasiva', [UnidadMedidaController::class, 'actualizacionmasiva']);

    
     // Rol permiso
     Route::get('/rolpermiso', [SigPermisoRolController::class, 'index']);    
     Route::post('/rolpermiso', [SigPermisoRolController::class, 'create']);    
     Route::post('/rolpermiso/{id}', [SigPermisoRolController::class, 'update']);    
     Route::delete('/rolpermiso/{id}', [SigPermisoRolController::class, 'destroy']); 
     Route::get('/rolespermisos', [RolController::class, 'rolesPermisos']); 

    
    // Permisos
    Route::get('/permisos', [PermisoController::class, 'index']);    
    
    // Contratos
    Route::get('/contratos/{cantidad}', [SigContratoController::class, 'index']);    
    Route::get('/contratosactivos', [SigContratoController::class, 'contratosactivos']);    
    Route::post('/contratos', [SigContratoController::class, 'create']);    
    Route::post('/contratos/{id}', [SigContratoController::class, 'update']);    
    Route::delete('/contratos/{id}', [SigContratoController::class, 'destroy']);    
    Route::get('/contratos/{id}', [SigContratoController::class, 'contratosusuario']);  
    Route::get('/contratosfiltro/{cadena}', [SigContratoController::class, 'filtro']);
    Route::get('/contratoslista', [SigContratoController::class, 'lista']);    
    Route::post('/contratosborradomasivo', [SigContratoController::class, 'borradomasivo']);
    Route::post('/contratosactualizacionmasiva', [SigContratoController::class, 'actualizacionmasiva']);  
    
    // Paises
    Route::get('/paises', [PaisController::class, 'index']);
    Route::post('/paises', [PaisController::class, 'create']);
    Route::post('/paises/{id}', [PaisController::class, 'update']);
    Route::delete('/paises/{id}', [PaisController::class, 'destroy']);

    // Departamentos
    Route::get('/departamentos', [DepartamentoController::class, 'index']);
    Route::post('/departamentos', [DepartamentoController::class, 'create']);
    Route::post('/departamentos/{id}', [DepartamentoController::class, 'update']);
    Route::delete('/departamentos/{id}', [DepartamentoController::class, 'destroy']);
    Route::get('/departamentos/{id}', [DepartamentoController::class, 'departamentopais']);
    
    // Municipios
    Route::get('/municipios', [MunicipioController::class, 'index']);
    Route::post('/municipios', [MunicipioController::class, 'create']);
    Route::post('/municipios/{id}', [MunicipioController::class, 'update']);
    Route::delete('/municipios/{id}', [MunicipioController::class, 'destroy']);
    Route::get('/municipios/{id}', [MunicipioController::class, 'municipiodepartamento']);

    // Zonas
    Route::get('/zonas/{cantidad}', [SigZonaController::class, 'index']);
    Route::post('/zonas', [SigZonaController::class, 'create']);
    Route::post('/zonas/{id}', [SigZonaController::class, 'update']);
    Route::delete('/zonas/{id}', [SigZonaController::class, 'destroy']);
    Route::get('/zonaslista', [SigZonaController::class, 'lista']);
    Route::get('/zonasfiltro/{cadena}', [SigZonaController::class, 'filtro']);
    Route::post('/zonasborradomasivo', [SigZonaController::class, 'borradomasivo']);
    Route::post('/zonasactualizacionmasiva', [SigZonaController::class, 'actualizacionmasiva']);

    // Route::get('/municipios/{id}', [SigZonaController::class, 'municipiodepartamento']);
    Route::get('/estadoempleados/{cantidad}', [SigEstadoEmpleadoController::class, 'index']);
    Route::post('/estadoempleados', [SigEstadoEmpleadoController::class, 'create']);
    Route::post('/estadoempleados/{id}', [SigEstadoEmpleadoController::class, 'update']);
    Route::delete('/estadoempleados/{id}', [SigEstadoEmpleadoController::class, 'destroy']);
    Route::get('/estadoempleadoslista', [SigEstadoEmpleadoController::class, 'lista']);
    Route::post('/estadoempleadosborradomasivo', [SigEstadoEmpleadoController::class, 'borradomasivo']);
    Route::post('/estadoempleadosactualizacionmasiva', [SigEstadoEmpleadoController::class, 'actualizacionmasiva']);
    
    // Tipos de documento de identidad
    Route::get('/tipodocumento/{cantidad}', [SigTipoDocumentoIdentidadController::class, 'index']);
    Route::post('/tipodocumento', [SigTipoDocumentoIdentidadController::class, 'create']);
    Route::post('/tipodocumento/{id}', [SigTipoDocumentoIdentidadController::class, 'update']);
    Route::delete('/tipodocumento/{id}', [SigTipoDocumentoIdentidadController::class, 'destroy']);
    Route::get('/tipodocumentolista', [SigTipoDocumentoIdentidadController::class, 'lista']);
    Route::post('/tipodocumentoborradomasivo', [SigTipoDocumentoIdentidadController::class, 'borradomasivo']);
    Route::post('/tipodocumentoactualizacionmasiva', [SigTipoDocumentoIdentidadController::class, 'actualizacionmasiva']);

    // Cargos
    Route::get('/cargos/{cantidad}', [SigCargoController::class, 'index']);
    Route::post('/cargos', [SigCargoController::class, 'create']);
    Route::post('/cargos/{id}', [SigCargoController::class, 'update']);
    Route::delete('/cargos/{id}', [SigCargoController::class, 'destroy']);
    Route::get('/cargoslista', [SigCargoController::class, 'lista']);
    Route::post('/cargosborradomasivo', [SigCargoController::class, 'borradomasivo']);
    Route::post('/cargosactualizacionmasiva', [SigCargoController::class, 'actualizacionmasiva']);

    // Ordenes de trabajo
    Route::get('/ordenestrabajo/{cantidad}', [SigOrdenTrabajoController::class, 'index']);
    Route::post('/ordenestrabajo', [SigOrdenTrabajoController::class, 'create']);
    Route::post('/ordenestrabajo/{id}', [SigOrdenTrabajoController::class, 'update']);
    Route::delete('/ordenestrabajo/{id}', [SigOrdenTrabajoController::class, 'destroy']);
    Route::get('/ordenestrabajo/{id}', [SigOrdenTrabajoController::class, 'otporcontrato']);
    Route::get('/ordenestrabajoasignadas/{cantidad}', [SigOrdenTrabajoController::class, 'asignadas']);
    Route::post('/ordenestrabajomasivo', [SigOrdenTrabajoController::class, 'cargaMasiva']);
    Route::post('/otsactualizacionmasiva', [SigOrdenTrabajoController::class, 'otsactualizacionmasiva']);
    Route::post('/otseliminacionmasiva', [SigOrdenTrabajoController::class, 'otseliminacionmasiva']);
    
    // Estados ordenes de trabajo
    Route::get('/estadoordenestrabajo/{cantidad}', [SigEstadoOrdenTrabajoController::class, 'index']);
    Route::post('/estadoordenestrabajo', [SigEstadoOrdenTrabajoController::class, 'create']);
    Route::post('/estadoordenestrabajo/{id}', [SigEstadoOrdenTrabajoController::class, 'update']);
    Route::get('/estadoordenestrabajolista', [SigEstadoOrdenTrabajoController::class, 'lista']);
    Route::delete('/estadoordenestrabajo/{id}', [SigEstadoOrdenTrabajoController::class, 'destroy']);
    Route::post('/estadoordenestrabajoborradomasivo', [SigEstadoOrdenTrabajoController::class, 'borradomasivo']);
    Route::post('/estadoordenestrabajoactualizacionmasiva', [SigEstadoOrdenTrabajoController::class, 'actualizacionmasiva']);
    
    // Formulario
    Route::get('/formulario', [SigFormularioController::class, 'index']);
    Route::post('/formulario', [SigFormularioController::class, 'create']);
    Route::post('/formulario/{id}', [SigFormularioController::class, 'update']);
    Route::delete('/formulario/{id}', [SigFormularioController::class, 'destroy']);
    Route::get('/formulario/{id}', [SigFormularioController::class, 'getbyid']);
    
    // Estados de los contratos
    Route::get('/estadocontrato/{cantidad}', [SigEstadoContratoController::class, 'index']);
    Route::post('/estadocontrato', [SigEstadoContratoController::class, 'create']);
    Route::post('/estadocontrato/{id}', [SigEstadoContratoController::class, 'update']);
    Route::delete('/estadocontrato/{id}', [SigEstadoContratoController::class, 'destroy']);
    Route::get('/estadocontratolista', [SigEstadoContratoController::class, 'lista']);
    Route::post('/estadocontratoborradomasivo', [SigEstadoContratoController::class, 'borradomasivo']);
    Route::post('/estadocontratoactualizacionmasiva', [SigEstadoContratoController::class, 'actualizacionmasiva']);
    
    // Empleados
    Route::get('/sigempleados/{cantidad}', [SigEmpleadoController::class, 'index']);
    Route::post('/sigempleados', [SigEmpleadoController::class, 'create']);
    Route::post('/sigempleados/{id}', [SigEmpleadoController::class, 'update']);
    Route::delete('/sigempleados/{id}', [SigEmpleadoController::class, 'destroy']);
    Route::get('/sigempleadosbyid/{id}', [SigEmpleadoController::class, 'empleadoById']);
    Route::get('/sigempleadosst', [SigEmpleadoController::class, 'getEmpleadosSST']);
    Route::get('/getEmpleadoEncargado', [SigEmpleadoController::class, 'getEmpleadoEncargado']);
    Route::get('/sigempleadoslista', [SigEmpleadoController::class, 'sigempleadoslista']);
    Route::get('/sigempleadosfiltro/{cadena}', [SigEmpleadoController::class, 'filtro']);
    Route::post('/sigempleadosborradomasivo', [SigEmpleadoController::class, 'borradomasivo']);
    Route::post('/sigempleadosactualizacionmasiva', [SigEmpleadoController::class, 'actualizacionmasiva']);

    // Contrato empleados
    Route::get('/contratoempleado/{cantidad}', [SigContratoEmpleadoController::class, 'index']);
    Route::post('/contratoempleado', [SigContratoEmpleadoController::class, 'create']);
    Route::post('/contratoempleado/{id}', [SigContratoEmpleadoController::class, 'update']);
    Route::delete('/contratoempleado/{id}', [SigContratoEmpleadoController::class, 'destroy']);
    Route::get('/contratoempleadofiltro/{cadena}', [SigContratoEmpleadoController::class, 'filtro']);
    Route::post('/contratoempleadoborradomasivo', [SigContratoEmpleadoController::class, 'borradomasivo']);
    Route::post('/contratoempleadoactualizacionmasiva', [SigContratoEmpleadoController::class, 'actualizacionmasiva']);

    // Ordenes de trabajo empleados
    Route::get('/ordentrabajoempleado', [SigOrdenTrabajoEmpleadoController::class, 'index']);
    Route::post('/ordentrabajoempleado', [SigOrdenTrabajoEmpleadoController::class, 'create']);
    Route::post('/ordentrabajoempleado/{id}', [SigOrdenTrabajoEmpleadoController::class, 'update']);
    Route::delete('/ordentrabajoempleado/{id}', [SigOrdenTrabajoEmpleadoController::class, 'destroy']);
    
    // Relación entre formularios y ots
    Route::get('/formularioordentrabajo', [SigFormularioOrdenTrabajoController::class, 'index']);
    Route::post('/formularioordentrabajo', [SigFormularioOrdenTrabajoController::class, 'create']);
    Route::post('/formularioordentrabajo/{id}', [SigFormularioOrdenTrabajoController::class, 'update']);
    Route::delete('/formularioordentrabajo/{id}', [SigFormularioOrdenTrabajoController::class, 'destroy']);
    Route::get('/formularioordentrabajo/{id}', [SigFormularioOrdenTrabajoController::class, 'findById']);

    // Costos de los items de ordenes de trabajo
    Route::get('/costoitem/{cantidad}', [CostoController::class, 'index']);
    Route::post('/costoitem', [CostoController::class, 'create']);
    Route::post('/costoitem/{id}', [CostoController::class, 'update']);
    Route::delete('/costoitem/{id}', [CostoController::class, 'destroy']);
    Route::get('/costoitemcategoria/{categoria}', [CostoController::class, 'costoitemcategoria']);
    Route::get('/costoitemfiltro/{cadena}', [CostoController::class, 'filtro']);
    Route::post('/costoitemborradomasivo', [CostoController::class, 'borradomasivo']);
    Route::post('/costoitemactualizacionmasiva', [CostoController::class, 'actualizacionmasiva']);
    Route::post('/costoitemmasivo', [CostoController::class, 'cargaMasiva']);
    // Route::get('/costoitem/{id}', [CostoController::class, 'findById']);


    // Categoría items ordenes de trabajo
    Route::get('/codigoitem/{cantidad}', [CodigoItemController::class, 'index']);
    Route::get('/listacodigoitem', [CodigoItemController::class, 'lista']);
    Route::post('/codigoitem', [CodigoItemController::class, 'create']);
    Route::post('/codigoitem/{id}', [CodigoItemController::class, 'update']);
    Route::delete('/codigoitem/{id}', [CodigoItemController::class, 'destroy']);
    Route::get('/codigoitem/{id}', [CodigoItemController::class, 'findById']);
    Route::post('/codigoitemborradomasivo', [CodigoItemController::class, 'borradomasivo']);
    Route::post('/codigoitemactualizacionmasiva', [CodigoItemController::class, 'actualizacionmasiva']);

    // Unidades de medida
    Route::get('/unidadmedida/{cantidad}', [UnidadMedidaController::class, 'index']);
    Route::get('/listaunidadmedida', [UnidadMedidaController::class, 'lista']);
    Route::post('/unidadmedida', [UnidadMedidaController::class, 'create']);
    Route::post('/unidadmedida/{id}', [UnidadMedidaController::class, 'update']);
    Route::delete('/unidadmedida/{id}', [UnidadMedidaController::class, 'destroy']);
    Route::get('/unidadmedida/{id}', [UnidadMedidaController::class, 'findById']);
    Route::get('/unidadmedida/{id}', [UnidadMedidaController::class, 'findById']);
    Route::post('/unidadmedidaborradomasivo', [UnidadMedidaController::class, 'borradomasivo']);
    Route::post('/unidadmedidaactualizacionmasiva', [UnidadMedidaController::class, 'actualizacionmasiva']);
    
    // Items
    Route::get('/itemsexport/{cadena}', [ItemController::class, 'export']);
    Route::get('/items/{cantidad}', [ItemController::class, 'index']);
    Route::get('/itemsfiltro/{cadena}', [ItemController::class, 'filtro']);

});
