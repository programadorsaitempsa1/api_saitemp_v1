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
use App\Http\Controllers\SigTipoDocumentoIdentidadController;
use App\Http\Controllers\AuthUsuarioController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\MenuRolController;
use App\Http\Controllers\SigPermisoRolController;
use App\Http\Controllers\MunicipioController;
use App\Http\Controllers\UnidadMedidaController;
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
use App\Http\Controllers\TipoMedidaDianController;
use App\Http\Controllers\LDAPUsersController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\CategoriaReporteController;
use App\Http\Controllers\SubcategoriaReporteController;
use App\Http\Controllers\ListaTrumpController;
use App\Http\Controllers\ProcesosEspecialesController;
use App\Http\Controllers\OperacionController;
use App\Http\Controllers\TipoPersonaController;
use App\Http\Controllers\EstratoController;
use App\Http\Controllers\CodigoCiiuController;
use App\Http\Controllers\ActividadCiiuController;
use App\Http\Controllers\SociedadComercialController;
use App\Http\Controllers\VendedorController;
use App\Http\Controllers\JornadaLaoralController;
use App\Http\Controllers\RotacionPersonalController;
use App\Http\Controllers\RiesgoLaboralController;
use App\Http\Controllers\ExamenController;
use App\Http\Controllers\RequisitoController;
use App\Http\Controllers\PeriodicidadLiquidacionController;
use App\Http\Controllers\TipoCuentaBancoController;
use App\Http\Controllers\OperacionInternacionalController;
use App\Http\Controllers\TipoOperacionInternacionalController;
use App\Http\Controllers\TipoOrigenFondoController;
use App\Http\Controllers\TiposOrigenMediosController;
use App\Http\Controllers\formularioDebidaDiligenciaController;
use App\Http\Controllers\ContratoController;
use App\Http\Controllers\TipoClienteController;
use App\Http\Controllers\TipoProveedorController;
use App\Http\Controllers\TipoDocumentoController;
use App\Http\Controllers\FormularioDDExportController;
use App\Http\Controllers\EnvioCorreoController;
use App\Http\Controllers\RegistroCorreosController;
use App\Http\Controllers\ConsultaCorreoController;
use App\Http\Controllers\CategoriaCargoController;
use App\Http\Controllers\SubCategoriaCargoController;
use App\Http\Controllers\ListaCargoController;
use App\Http\Controllers\ListaExamenController;
use App\Http\Controllers\CargoClienteController;
use App\Http\Controllers\ListaRecomendacionController;
use App\Http\Controllers\ClientesAlInstanteController;
use App\Http\Controllers\ListaConceptosFormularioSupController;
use App\Http\Controllers\formularioSupervisionController;
use App\Http\Controllers\EstadosConceptoFormularioSupController;
use App\Http\Controllers\ServicioOrdenServicioController;
use App\Http\Controllers\BonificacionOrdenServicioController;
use App\Http\Controllers\EstadoOrdenServicioController;
use App\Http\Controllers\LaboratorioOrdenServicioController;
use App\Http\Controllers\OrdenServiciolienteController;
use App\Http\Controllers\OrdenServicioServicioSolicitadoController;
use App\Http\Controllers\OrdenServicioHojaVidaController;
use App\Http\Controllers\OrdenServicioCargoController;
use App\Http\Controllers\OrdenServicioBonificacionController;
use App\Http\Controllers\OservicioCargoController;
use App\Http\Controllers\OservicioHojaVidaController;
use App\Http\Controllers\OservicioClienteController;
use App\Http\Controllers\DashBoardSeleccionController;
use App\Http\Controllers\OservicioEstadoCargoController;
use App\Http\Controllers\UsuarioPermisoController;
use App\Http\Controllers\UsuariosMenusController;



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
  'middleware' => ['api', \Fruitcake\Cors\HandleCors::class],
  'prefix' => 'v1'

], function ($router) {

  Route::post('/login', [AuthController::class, 'login']);
  Route::post('/register', [AuthController::class, 'register']);
  // Route::post('/register2', [AuthUsuarioController::class, 'register']);
  Route::post('/logout', [AuthController::class, 'logout']);
  Route::post('/refresh', [AuthController::class, 'refresh']);
  Route::get('/user-profile', [AuthController::class, 'userProfile']);

  // Usuarios
  Route::get('/users/{cantidad}', [UsuarioController::class, 'index']);
  Route::get('/users/{filtro}/{cantidad}', [UsuarioController::class, 'filtro']);
  Route::get('/userslist', [UsuarioController::class, 'userslist']);
  Route::get('/userlogued', [UsuarioController::class, 'userlogued']);
  Route::get('/userbyid/{id}', [UsuarioController::class, 'userById']);
  Route::delete('/user/{id}', [UsuarioController::class, 'destroy']);
  // Route::post('/user', [UsuarioController::class, 'create']); 
  Route::post('/user', [UsuarioController::class, 'update']);
  // Route::get('/usuariosporcontrato', [UsuarioController::class, 'usuariosporcontrato']); 
  // Route::get('/usuariosporcontrato/{id}', [UsuarioController::class, 'usuariosporcontrato2']); 

  // Opciones de menú
  Route::get('/menuslista', [MenuController::class, 'index']);
  Route::post('/menus', [MenuController::class, 'create']);
  Route::post('/menus/{id}', [MenuController::class, 'update']);
  Route::delete('/menus/{id}', [MenuController::class, 'destroy']);
  Route::get('/menus', [MenuController::class, 'menubyRole']);
  Route::get('/categoriaMenu', [MenuController::class, 'categoriaMenu']);

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

  // Género
  Route::get('/genero', [GeneroController::class, 'index']);

  Route::post('/enviocorreo', [EnvioCorreoController::class, 'sendEmail']);
  Route::post('/authUser', [EnvioCorreoController::class, 'authUser']);

  Route::get('/consultacorreo/{cantidad}', [RegistroCorreosController::class, 'index']);
  Route::post('/registrocorreo', [RegistroCorreosController::class, 'create']);
  Route::get('/consultacorreofiltro/{cadena}', [RegistroCorreosController::class, 'correosfiltro']);

  // Dahsboard
  Route::get('/empleadosactivos', [DashboardController::class, 'empleadosactivos']);
  Route::get('/empleadosplanta', [DashboardController::class, 'empleadosplanta']);
  Route::get('/ingresosmescurso', [DashboardController::class, 'ingresosmescurso']);
  Route::get('/retirosmescurso', [DashboardController::class, 'retirosmescurso']);
  Route::get('/ingresosmesanterior', [DashboardController::class, 'ingresosmesanterior']);
  Route::get('/retirosmesantrior', [DashboardController::class, 'retirosmesantrior']);
  Route::get('/historicoempleado/{cedula}/{cantidad}', [DashboardController::class, 'historicoempleado']);
  Route::get('/analista/{id}', [DashboardController::class, 'analista']);
  Route::get('/historicoempleadoexport/{filtro}', [DashboardController::class, 'historicoempleadoexport']);
  Route::get('/datosempleado/{cedula}', [DashboardController::class, 'datosempleado']);
  Route::get('/username/{cedula}', [DashboardController::class, 'username']);

  // Exporte formulario debida diligencia
  // Route::get('/formularioddexport/{id}', [FormularioDDExportController::class, 'export']); 
  Route::get('/exportaformulariocliente/{cadena}', [FormularioDDExportController::class, 'export2']);

  // Estado civil
  Route::get('/estadocivil', [EstadoCivilController::class, 'index']);

  // Forma de pago
  Route::get('/formapago', [FormaPagoController::class, 'index']);

  // Banco
  Route::get('/banco', [BancoController::class, 'index']);

  // Tipo de contrato
  Route::get('/tipocontrato', [TipoContratoController::class, 'index']);

  // Estado laboral empleado
  Route::get('/estadolaboralempleado', [EstadoLaboralEmpleadoController::class, 'index']);

  // Convenio
  Route::get('/convenio', [ConvenioController::class, 'index']);
  Route::get('/convenio/{texto}', [ConvenioController::class, 'search']);

  // Empleado
  Route::get('/empleado', [EmpleadoController::class, 'index']);
  Route::get('/empleado/{texto}', [EmpleadoController::class, 'search']);

  // Sucursales
  Route::get('/sucursalss', [SucursalSSController::class, 'index']);

  // Compañia
  Route::get('/compania', [CompaniaController::class, 'index']);


  // Sucursal
  Route::get('/sucursal', [SucursalController::class, 'index']);

  // Cnetro de costos
  Route::get('/centrocostos', [CentroCostosController::class, 'index']);

  // Area
  Route::get('/area', [AreaController::class, 'index']);

  // Centro de trabajo
  Route::get('/centrotrabajo', [CentroTrabajoController::class, 'index']);
  Route::get('/centrotrabajo/{texto}', [CentroTrabajoController::class, 'search']);

  // Cuenta gastos local
  Route::get('/cuentagastosl', [CuentaGastosLController::class, 'index']);

  // Cargo
  Route::get('/cargo', [CargoController::class, 'index']);

  // Modo liquidación
  Route::get('/modoliquidacion', [ModoLiquidacionController::class, 'index']);

  // Clase salario
  Route::get('/clasesalario', [ClaseSalarioController::class, 'index']);

  // Tipo cotizante
  Route::get('/tipocotizante', [TipoCotizanteController::class, 'index']);

  // Subtipo cotizante
  Route::get('/subtipocotizante', [SubTipoCotizanteController::class, 'index']);

  // Tipo medida Dian
  Route::get('/tipomedidadian', [TipoMedidaDianController::class, 'index']);

  // Reporte
  Route::get('/reportes/{cantidad}', [ReporteController::class, 'index']);
  Route::get('/reportes/{aplicacion}/{categoria}/{cantidad}', [ReporteController::class, 'filtrado']);

  // Categoria reporte
  Route::get('/categoriasreporte', [CategoriaReporteController::class, 'index']);

  // Categoria reporte
  Route::get('/subcategoriasreporte/{codigo}', [SubcategoriaReporteController::class, 'index']);

  // Lista trump
  Route::get('/listatrump/{codigo}', [ListaTrumpController::class, 'index']);

  // Procesos especiales
  Route::get('/procesosespeciales', [ProcesosEspecialesController::class, 'index']);
  Route::get('/formprocesosespeciales/{codigo}', [ProcesosEspecialesController::class, 'form']);
  Route::get('/listasprocesosespeciales/{codigo}/{codigo1}/{codigo2}', [ProcesosEspecialesController::class, 'listasprocesosespeciales']);
  Route::get('/filtroprocesosespeciales/{codigo}/{search}/{codigo1}/{codigo2}', [ProcesosEspecialesController::class, 'filtroprocesosespeciales']);
  Route::get('/ejecutaprocesosespeciales', [ProcesosEspecialesController::class, 'ejecutaprocesosespeciales']);
  Route::get('/procesosespecialesexport/{filtro}', [ProcesosEspecialesController::class, 'procesosespecialesexport']);
  // Route::get('/ejecutaprocesosespeciales/{request}', [ProcesosEspecialesController::class, 'ejecutaprocesosespeciales2']);

  // Tipo medida Dian
  Route::get('/ldapusers/{cantidad}', [LDAPUsersController::class, 'index']);
  Route::post('/ldapusers', [LDAPUsersController::class, 'create']);
  Route::get('/ldapuserfilter/{user}', [LDAPUsersController::class, 'userById']);

  // fondo salud, pensión, caja compensación, riesgo laboral, fondo cesantias
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
  Route::get('/rolpermiso/{cantidad}', [SigPermisoRolController::class, 'index']);
  Route::get('/filtrorol/{id}/{cantidad}', [SigPermisoRolController::class, 'filtrorol']);
  Route::post('/rolpermiso', [SigPermisoRolController::class, 'create']);
  Route::post('/rolpermiso/{id}', [SigPermisoRolController::class, 'update']);
  Route::post('/rolpermisoborradomasivo', [SigPermisoRolController::class, 'borradomasivo']);
  Route::delete('/rolpermiso/{id}', [SigPermisoRolController::class, 'destroy']);
  Route::get('/rolespermisos', [RolController::class, 'rolesPermisos']);

  
  // Usuario permiso
  Route::get('/usuariopermiso/{cantidad}', [UsuarioPermisoController::class, 'index']);
  Route::post('/usuariopermiso', [UsuarioPermisoController::class, 'create']);
  Route::post('/usuariopermiso/{id}', [UsuarioPermisoController::class, 'update']);
  Route::post('/usuariopermisoborradomasivo', [UsuarioPermisoController::class, 'borradomasivo']);
  Route::delete('/usuariopermiso/{id}', [UsuarioPermisoController::class, 'destroy']);
  Route::get('/filtroporusuario/{id}/{cantidad}', [UsuarioPermisoController::class, 'filtroporusuario']);

  // Usuarios menús
  Route::get('/usuariosmenus/{cantidad}', [UsuariosMenusController::class, 'index']);
  Route::post('/usuariosmenus', [UsuariosMenusController::class, 'create']);
  Route::post('/usuariosmenus/{id}', [UsuariosMenusController::class, 'update']);
  Route::post('/usuariosmenusborradomasivo', [UsuariosMenusController::class, 'borradomasivo']);
  Route::delete('/usuariosmenus/{id}', [UsuariosMenusController::class, 'destroy']);
  Route::get('/filtromenuporusuario/{id}/{cantidad}', [UsuariosMenusController::class, 'filtroporusuario']);

  // Permisos
  Route::get('/permisos/{cantidad}', [PermisoController::class, 'index']);
  Route::get('/permisos', [PermisoController::class, 'byId']);
  Route::get('/permisoslista', [PermisoController::class, 'permisoslista']);
  Route::post('/permisos', [PermisoController::class, 'create']);
  Route::post('/permisos/{id}', [PermisoController::class, 'update']);
  Route::delete('/permisos/{id}', [PermisoController::class, 'destroy']);


  // Tipo de cliente
  Route::get('/tipocliente', [TipoClienteController::class, 'index']);

  // Tipo de proveedor
  Route::get('/tipoproveedor', [TipoProveedorController::class, 'index']);

  // Tipo de cliente
  Route::get('/tipoarchivo', [TipoDocumentoController::class, 'index']);
  Route::get('/tipoarchivo/{id}', [TipoDocumentoController::class, 'byid']);

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

  // Tipos de operación
  Route::get('/operacion', [OperacionController::class, 'index']);
  Route::post('/operacion', [OperacionController::class, 'create']);
  Route::post('/operacion/{id}', [OperacionController::class, 'update']);
  Route::delete('/operacion/{id}', [OperacionController::class, 'destroy']);

  // Tipos de persona
  Route::get('/tipopersona', [TipoPersonaController::class, 'index']);
  Route::post('/tipopersona', [TipoPersonaController::class, 'create']);
  Route::post('/tipopersona/{id}', [TipoPersonaController::class, 'update']);
  Route::delete('/tipopersona/{id}', [TipoPersonaController::class, 'destroy']);

  // Estartos socioeconómicos
  Route::get('/estrato', [EstratoController::class, 'index']);
  Route::post('/estrato', [EstratoController::class, 'create']);
  Route::post('/estrato/{id}', [EstratoController::class, 'update']);
  Route::delete('/estrato/{id}', [EstratoController::class, 'destroy']);

  // Codigos ciiu
  Route::get('/codigociiu', [CodigoCiiuController::class, 'index']);
  Route::post('/codigociiu', [CodigoCiiuController::class, 'create']);
  Route::post('/codigociiu/{id}', [CodigoCiiuController::class, 'update']);
  Route::delete('/codigociiu/{id}', [CodigoCiiuController::class, 'destroy']);

  // Actividades ciiu
  Route::get('/actividadciiu', [ActividadCiiuController::class, 'index']);
  Route::get('/actividadciiu/{id}', [ActividadCiiuController::class, 'activityBycode']);
  Route::post('/actividadciiu', [ActividadCiiuController::class, 'create']);
  Route::post('/actividadciiu/{id}', [ActividadCiiuController::class, 'update']);
  Route::delete('/actividadciiu/{id}', [ActividadCiiuController::class, 'destroy']);
  Route::get('/actividadciiu/filetr/{id}', [ActividadCiiuController::class, 'filter']);

  // Tipos de operación
  Route::get('/sociedadcomercial', [SociedadComercialController::class, 'index']);
  Route::post('/sociedadcomercial', [SociedadComercialController::class, 'create']);
  Route::post('/sociedadcomercial/{id}', [SociedadComercialController::class, 'update']);
  Route::delete('/sociedadcomercial/{id}', [SociedadComercialController::class, 'destroy']);

  // Ejecutivos comerciales
  Route::get('/ejecutivocomercial', [VendedorController::class, 'index']);
  Route::get('/ejecutivocomerciallista', [VendedorController::class, 'lista']);
  Route::post('/ejecutivocomercial', [VendedorController::class, 'create']);
  Route::post('/ejecutivocomercial/{id}', [VendedorController::class, 'update']);
  Route::delete('/ejecutivocomercial/{id}', [VendedorController::class, 'destroy']);

  // Jornadas laborales
  Route::get('/jornadalaboral', [JornadaLaoralController::class, 'index']);
  Route::post('/jornadalaboral', [JornadaLaoralController::class, 'create']);
  Route::post('/jornadalaboral/{id}', [JornadaLaoralController::class, 'update']);
  Route::delete('/jornadalaboral/{id}', [JornadaLaoralController::class, 'destroy']);

  // Rotaciones de personal
  Route::get('/rotacionpersonal', [RotacionPersonalController::class, 'index']);
  Route::post('/rotacionpersonal', [RotacionPersonalController::class, 'create']);
  Route::post('/rotacionpersonal/{id}', [RotacionPersonalController::class, 'update']);
  Route::delete('/rotacionpersonal/{id}', [RotacionPersonalController::class, 'destroy']);

  // Riesgos laborales
  Route::get('/riesgolaboral', [RiesgoLaboralController::class, 'index']);
  Route::post('/riesgolaboral', [RiesgoLaboralController::class, 'create']);
  Route::post('/riesgolaboral/{id}', [RiesgoLaboralController::class, 'update']);
  Route::delete('/riesgolaboral/{id}', [RiesgoLaboralController::class, 'destroy']);

  // Exámenes médicos del cargo
  Route::get('/examen', [ExamenController::class, 'index']);
  Route::post('/examen', [ExamenController::class, 'create']);
  Route::post('/examen/{id}', [ExamenController::class, 'update']);
  Route::delete('/examen/{id}', [ExamenController::class, 'destroy']);

  // Requisitos del cargo
  Route::get('/requisito', [RequisitoController::class, 'index']);
  Route::post('/requisito', [RequisitoController::class, 'create']);
  Route::post('/requisito/{id}', [RequisitoController::class, 'update']);
  Route::delete('/requisito/{id}', [RequisitoController::class, 'destroy']);

  // Requisitos del cargo
  Route::get('/listarecomendaciones/{id}', [ListaRecomendacionController::class, 'index']);
  Route::post('/listarecomendaciones', [ListaRecomendacionController::class, 'create']);
  Route::post('/listarecomendaciones/{id}', [ListaRecomendacionController::class, 'update']);
  Route::delete('/listarecomendaciones/{id}', [ListaRecomendacionController::class, 'destroy']);

  // Periodicidades para liquidación
  Route::get('/periodicidadliquidacion', [PeriodicidadLiquidacionController::class, 'index']);
  Route::post('/periodicidadliquidacion', [PeriodicidadLiquidacionController::class, 'create']);
  Route::post('/periodicidadliquidacion/{id}', [PeriodicidadLiquidacionController::class, 'update']);
  Route::delete('/periodicidadliquidacion/{id}', [PeriodicidadLiquidacionController::class, 'destroy']);

  // Tipos de cuenta bancaria
  Route::get('/tipocuentabanco', [TipoCuentaBancoController::class, 'index']);
  Route::post('/tipocuentabanco', [TipoCuentaBancoController::class, 'create']);
  Route::post('/tipocuentabanco/{id}', [TipoCuentaBancoController::class, 'update']);
  Route::delete('/tipocuentabanco/{id}', [TipoCuentaBancoController::class, 'destroy']);

  // Categorias cargos
  Route::get('/categoriacargo', [CategoriaCargoController::class, 'index']);
  Route::post('/categoriacargo', [CategoriaCargoController::class, 'create']);
  Route::post('/categoriacargo/{id}', [CategoriaCargoController::class, 'update']);
  Route::delete('/categoriacargo/{id}', [CategoriaCargoController::class, 'destroy']);


  // Subcategorias cargos
  Route::get('/subcategoriacargo/{id}', [SubCategoriaCargoController::class, 'index']);
  Route::post('/subcategoriacargo', [SubCategoriaCargoController::class, 'create']);
  Route::post('/subcategoriacargo/{id}', [SubCategoriaCargoController::class, 'update']);
  Route::delete('/subcategoriacargo/{id}', [SubCategoriaCargoController::class, 'destroy']);

  // lista cargos
  Route::get('/listacargos/{id}', [ListaCargoController::class, 'index']);
  Route::post('/listacargos', [ListaCargoController::class, 'create']);
  Route::post('/listacargos/{id}', [ListaCargoController::class, 'update']);
  Route::delete('/listacargos/{id}', [ListaCargoController::class, 'destroy']);

  // lista examenes
  Route::get('/listaexamenes/{id}', [ListaExamenController::class, 'index']);
  Route::post('/listaexamenes', [ListaExamenController::class, 'create']);
  Route::post('/listaexamenes/{id}', [ListaExamenController::class, 'update']);
  Route::delete('/listaexamenes/{id}', [ListaExamenController::class, 'destroy']);

  // Cargos cliente
  Route::get('/cargoscliente', [CargoClienteController::class, 'index']);
  Route::post('/cargoscliente', [CargoClienteController::class, 'create']);
  Route::post('/cargoscliente/{id}', [CargoClienteController::class, 'update']);
  Route::delete('/cargoscliente/{id}', [CargoClienteController::class, 'destroy']);

  // Tipos de operaciones internacionales
  Route::get('/operacioninternacional', [OperacionInternacionalController::class, 'index']);
  Route::post('/operacioninternacional', [OperacionInternacionalController::class, 'create']);
  Route::post('/operacioninternacional/{id}', [OperacionInternacionalController::class, 'update']);
  Route::delete('/operacioninternacional/{id}', [OperacionInternacionalController::class, 'destroy']);

  // Operaciones internacionales
  Route::get('/tipooperacioninternacional', [TipoOperacionInternacionalController::class, 'index']);
  Route::post('/tipooperacioninternacional', [TipoOperacionInternacionalController::class, 'create']);
  Route::post('/tipooperacioninternacional/{id}', [TipoOperacionInternacionalController::class, 'update']);
  Route::delete('/tipooperacioninternacional/{id}', [TipoOperacionInternacionalController::class, 'destroy']);

  // Tipos de origen de fondo
  Route::get('/tipoorigenfondo', [TipoOrigenFondoController::class, 'index']);
  Route::post('/tipoorigenfondo', [TipoOrigenFondoController::class, 'create']);
  Route::post('/tipoorigenfondo/{id}', [TipoOrigenFondoController::class, 'update']);
  Route::delete('/tipoorigenfondo/{id}', [TipoOrigenFondoController::class, 'destroy']);

  // Tipos de origen de medio
  Route::get('/tipoorigenmedio', [TiposOrigenMediosController::class, 'index']);
  Route::post('/tipoorigenmedio', [TiposOrigenMediosController::class, 'create']);
  Route::post('/tipoorigenmedio/{id}', [TiposOrigenMediosController::class, 'update']);
  Route::delete('/tipoorigenmedio/{id}', [TiposOrigenMediosController::class, 'destroy']);

  // Formularios registro clientes
  Route::get('/formulariocliente', [formularioDebidaDiligenciaController::class, 'index']);
  Route::get('/formulariocliente/{id}', [formularioDebidaDiligenciaController::class, 'getbyid']);
  Route::get('/clienteexist/{id}/{tipo_id}', [formularioDebidaDiligenciaController::class, 'existbyid']);
  Route::post('/formulariocliente', [formularioDebidaDiligenciaController::class, 'create']);
  Route::post('/formulariocliente/doc/{id}', [formularioDebidaDiligenciaController::class, 'store']);
  Route::post('/formulariocliente/{id}', [formularioDebidaDiligenciaController::class, 'update']);
  Route::delete('/formulariocliente/{id}', [formularioDebidaDiligenciaController::class, 'destroy']);

  Route::get('/consultaformulariocliente/{cantidad}', [formularioDebidaDiligenciaController::class, 'consultacliente']);
  Route::get('/clientesactivos', [formularioDebidaDiligenciaController::class, 'clientesactivos']);
  Route::get('/consultaformularioclientefiltro/{cadena}', [formularioDebidaDiligenciaController::class, 'filtro']);

  Route::get('/contrato/{id}', [ContratoController::class, 'index']);

  // Tipos de documento de identidad
  Route::get('/tipodocumento/{cantidad}', [SigTipoDocumentoIdentidadController::class, 'index']);
  Route::post('/tipodocumento', [SigTipoDocumentoIdentidadController::class, 'create']);
  Route::post('/tipodocumento/{id}', [SigTipoDocumentoIdentidadController::class, 'update']);
  Route::delete('/tipodocumento/{id}', [SigTipoDocumentoIdentidadController::class, 'destroy']);
  Route::get('/tipodocumentolista', [SigTipoDocumentoIdentidadController::class, 'lista']);
  Route::post('/tipodocumentoborradomasivo', [SigTipoDocumentoIdentidadController::class, 'borradomasivo']);
  Route::post('/tipodocumentoactualizacionmasiva', [SigTipoDocumentoIdentidadController::class, 'actualizacionmasiva']);

  // Clientes al instante
  Route::get('/clientesai', [ClientesAlInstanteController::class, 'index']);
  Route::get('/clientesalinstante/filter/{texto}', [ClientesAlInstanteController::class, 'filter']);
  Route::post('/clientesai', [ClientesAlInstanteController::class, 'create']);
  Route::post('/clientesai/{id}', [ClientesAlInstanteController::class, 'update']);
  Route::delete('/clientesai/{id}', [ClientesAlInstanteController::class, 'destroy']);

  // Clientes al instante
  Route::get('/conceptosformulario', [ListaConceptosFormularioSupController::class, 'index']);
  Route::get('/lementospp', [ListaConceptosFormularioSupController::class, 'lementospp']);
  Route::post('/conceptosformulario', [ListaConceptosFormularioSupController::class, 'create']);
  Route::post('/conceptosformulario/{id}', [ListaConceptosFormularioSupController::class, 'update']);
  Route::delete('/conceptosformulario/{id}', [ListaConceptosFormularioSupController::class, 'destroy']);

  // Clientes al instante
  Route::get('/formulariosupervision', [formularioSupervisionController::class, 'index']);
  Route::get('/formulariosupervision/{id}', [formularioSupervisionController::class, 'formById']);
  Route::post('/formulariosupervision', [formularioSupervisionController::class, 'create']);
  Route::post('/formulariosupervision/{id}', [formularioSupervisionController::class, 'update']);
  Route::delete('/formulariosupervision/{id}', [formularioSupervisionController::class, 'destroy']);
  Route::get('/crearPdf/{id}', [formularioSupervisionController::class, 'crearPdf']);

  // Estados concepto formulario de supervisión
  Route::get('/estadosconceptoformulario', [EstadosConceptoFormularioSupController::class, 'index']);
  Route::get('/estadoseppformulario', [EstadosConceptoFormularioSupController::class, 'estadosepp']);
  Route::post('/estadosconceptoformulario', [EstadosConceptoFormularioSupController::class, 'create']);
  Route::post('/estadosconceptoformulario/{id}', [EstadosConceptoFormularioSupController::class, 'update']);
  Route::delete('/estadosconceptoformulario/{id}', [EstadosConceptoFormularioSupController::class, 'destroy']);

  // Servicios orden de servicio
  Route::get('/serviciosordens', [ServicioOrdenServicioController::class, 'index']);
  Route::post('/serviciosordens', [ServicioOrdenServicioController::class, 'create']);
  Route::post('/serviciosordens/{id}', [ServicioOrdenServicioController::class, 'update']);
  Route::delete('/serviciosordens/{id}', [ServicioOrdenServicioController::class, 'destroy']);

  // Bonificación orden de servicio
  Route::get('/bonificacionordens', [BonificacionOrdenServicioController::class, 'index']);
  Route::post('/bonificacionordens', [BonificacionOrdenServicioController::class, 'create']);
  Route::post('/bonificacionordens/{id}', [BonificacionOrdenServicioController::class, 'update']);
  Route::delete('/bonificacionordens/{id}', [BonificacionOrdenServicioController::class, 'destroy']);

  // Estados orden de servicio
  Route::get('/estadoordens', [EstadoOrdenServicioController::class, 'index']);
  Route::post('/estadoordens', [EstadoOrdenServicioController::class, 'create']);
  Route::post('/estadoordens/{id}', [EstadoOrdenServicioController::class, 'update']);
  Route::delete('/estadoordens/{id}', [EstadoOrdenServicioController::class, 'destroy']);

  // Laboratorios orden de servicio
  Route::get('/laboratorioos', [LaboratorioOrdenServicioController::class, 'index']);
  Route::post('/laboratorioos', [LaboratorioOrdenServicioController::class, 'create']);
  Route::post('/laboratorioos/{id}', [LaboratorioOrdenServicioController::class, 'update']);
  Route::delete('/laboratorioos/{id}', [LaboratorioOrdenServicioController::class, 'destroy']);

  //   OrdenServiciolienteController
  Route::get('/ordenserviciocliente', [OrdenServiciolienteController::class, 'index']);
  Route::post('/ordenserviciocliente', [OrdenServiciolienteController::class, 'create']);
  Route::post('/ordenserviciocliente/{id}', [OrdenServiciolienteController::class, 'update']);
  Route::delete('/ordenserviciocliente/{id}', [OrdenServiciolienteController::class, 'destroy']);

  // OrdenServicioServicioSolicitadoController
  Route::get('/ordenserviciosolicitado', [OrdenServicioServicioSolicitadoController::class, 'index']);
  Route::post('/ordenserviciosolicitado', [OrdenServicioServicioSolicitadoController::class, 'create']);
  Route::post('/ordenserviciosolicitado/{id}', [OrdenServicioServicioSolicitadoController::class, 'update']);
  Route::delete('/ordenserviciosolicitado/{id}', [OrdenServicioServicioSolicitadoController::class, 'destroy']);

  // OrdenServicioHojaVidaController
  Route::get('/ordenserviciohojavida', [OrdenServicioHojaVidaController::class, 'index']);
  Route::post('/ordenserviciohojavida', [OrdenServicioHojaVidaController::class, 'create']);
  Route::post('/ordenserviciohojavida/{id}', [OrdenServicioHojaVidaController::class, 'update']);
  Route::delete('/ordenserviciohojavida/{id}', [OrdenServicioHojaVidaController::class, 'destroy']);

  // OrdenServicioCargoController
  Route::get('/ordenserviciocargo', [OrdenServicioCargoController::class, 'index']);
  Route::post('/ordenserviciocargo', [OrdenServicioCargoController::class, 'create']);
  Route::post('/ordenserviciocargo/{id}', [OrdenServicioCargoController::class, 'update']);
  Route::delete('/ordenserviciocargo/{id}', [OrdenServicioCargoController::class, 'destroy']);

  // OrdenServicioBonificacionController
  Route::get('/ordenserviciobonificacion', [OrdenServicioBonificacionController::class, 'index']);
  Route::post('/ordenserviciobonificacion', [OrdenServicioBonificacionController::class, 'create']);
  Route::post('/ordenserviciobonificacion/{id}', [OrdenServicioBonificacionController::class, 'update']);
  Route::delete('/ordenserviciobonificacion/{id}', [OrdenServicioBonificacionController::class, 'destroy']);

  // oservicio estado cargo
  Route::get('/oservicioestadocargo', [OservicioEstadoCargoController::class, 'index']);
  Route::post('/oservicioestadocargo', [OservicioEstadoCargoController::class, 'create']);
  Route::post('/oservicioestadocargo/{id}', [OservicioEstadoCargoController::class, 'update']);
  Route::delete('/oservicioestadocargo/{id}', [OservicioEstadoCargoController::class, 'destroy']);

  // ordenserviciocliente
  Route::get('/ordenserviciocliente', [OservicioClienteController::class, 'index']);
  Route::get('/ordenservicioclientetabla/{cantidad}', [OservicioClienteController::class, 'tabla']);
  Route::get('/ordenserviciocliente/{id}', [OservicioClienteController::class, 'getClienteCompleto']);
  Route::post('/ordenserviciocliente', [OservicioClienteController::class, 'create']);
  Route::put('/ordenserviciocliente/{id}', [OservicioClienteController::class, 'update']);
  Route::delete('/ordenserviciocliente/{id}', [OservicioClienteController::class, 'destroy']);

  // ordenserviciocargo
  Route::get('/ordenserviciocargo', [OservicioCargoController::class, 'index']);
  Route::get('/ordenserviciocargochar/{anio}', [OservicioCargoController::class, 'cargoschar']);
  Route::get('/vacantesEfectivas/{anio}', [OservicioCargoController::class, 'vacantesEfectivas']);
  Route::get('/ordenserviciocargocantidadchar/{anio}', [OservicioCargoController::class, 'cargosCantidadchar']);
  Route::get('/ordenserviciocargocantidadchar2/{anio}', [OservicioCargoController::class, 'cargosCantidadchar2']);
  Route::post('/ordenserviciocargo/{id}', [OservicioCargoController::class, 'create']);
  Route::put('/ordenserviciocargo/{id}', [OservicioCargoController::class, 'update']);
  Route::delete('/ordenserviciocargo/{id}', [OservicioCargoController::class, 'destroy']);

  // ordenserviciohojavida
  Route::get('/ordenserviciohojavida', [OservicioHojaVidaController::class, 'index']);
  Route::get('/ordenserviciohojavidachar/{anio}', [OservicioHojaVidaController::class, 'HojaVidaChar']);
  Route::post('/ordenserviciohojavida/{id}', [OservicioHojaVidaController::class, 'create']);
  Route::put('/ordenserviciohojavida/{id}', [OservicioHojaVidaController::class, 'update']);
  Route::delete('/ordenserviciohojavida/{id}', [OservicioHojaVidaController::class, 'destroy']);

  Route::get('/cargosVacantesHojasVida/{anio}', [DashBoardSeleccionController::class, 'cargosVacantesHojasVida']);
  Route::get('/cantidadvacantesestado/{anio}', [DashBoardSeleccionController::class, 'cantidadVacantesPorEstado']);
});
