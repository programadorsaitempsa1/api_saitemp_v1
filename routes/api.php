<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Http\Request;
use App\Http\Controllers\FiltroCrmController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiFirmaElectronicaController;
use App\Http\Controllers\HistoricoEstadosDdController;
use App\Http\Controllers\FamiliaresFormEmpleadoController;
use App\Http\Controllers\GrupoEtnicoFormEmpleadoController;
use App\Http\Controllers\NivelAcademicoFormEmpleadoController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BancosFormularioEmpleadoController;
use App\Http\Controllers\TipoIdFormularioEmpleadoController;
use App\Http\Controllers\DepartamentosFormularioEmpleadoController;
use App\Http\Controllers\CiudadesFormularioEmpleadoController;
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
use App\Http\Controllers\categoriaMenuController;
use App\Http\Controllers\NivelAccidentalidadController;
use App\Http\Controllers\ElementosPPController;
use App\Http\Controllers\EstadosFirmaController;
use App\Http\Controllers\RegistroCambioController;
use App\Http\Controllers\ExamenPruebaController;
use App\Http\Controllers\SeleccionModalController;
use App\Http\Controllers\ContratacionModalController;
use App\Http\Controllers\PaisesFormualrioEmpleadoController;
use App\Http\Controllers\OtroSiController;
use App\Http\Controllers\CiudadLaboratorioController;
use App\Http\Controllers\AtencionInteraccionController;
use App\Http\Controllers\ProcesosController;
use App\Http\Controllers\ClienteInteraccionController;
use App\Http\Controllers\ArchivosFormularioIngresoController;
use App\Http\Controllers\AfpFormularioIngresoController;
use App\Http\Controllers\CargosPlantaController;
use App\Http\Controllers\formularioGestionIngresoController;
use App\Http\Controllers\estadosIngresoController;
use App\Http\Controllers\FormularioIngresoTipoServicioController;
use App\Http\Controllers\formularioIngresoExportController;
use App\Http\Controllers\ObservacionEstadoFormIngresoController;
use App\Http\Controllers\SedeController;
use App\Http\Controllers\SolicitanteCrmController;
use App\Http\Controllers\EstadoCierreCrmController;
use App\Http\Controllers\PqrsfCRMController;
use App\Http\Controllers\SeguimientoCrmController;
use App\Http\Controllers\IndicadoresSeyaController;
use App\Http\Controllers\CuentaRegresivaController;
use App\Http\Controllers\ModalPrincipalController;
use App\Http\Controllers\RiesgosNivelImpactoController;
use App\Http\Controllers\RiesgosNivelProbabilidadController;
use App\Http\Controllers\RiesgosTiposProcesoController;
use App\Http\Controllers\RiesgosNombresProcesoController;
use App\Http\Controllers\RiesgosMetodosIdentificacionController;
use App\Http\Controllers\RiesgoFactoresController;
use App\Http\Controllers\RiesgoSeguimientosController;
use App\Http\Controllers\RiesgoDocumentosRegistradosController;
use App\Http\Controllers\RiesgoClasesControlController;
use App\Http\Controllers\RiesgoFrecuenciasControlController;
use App\Http\Controllers\RiesgoExisteEvidenciasController;
use App\Http\Controllers\RiesgoTiposControlController;
use App\Http\Controllers\RiesgoEjecucionesEficacesController;
use App\Http\Controllers\RiesgoControlController;
use App\Http\Controllers\MatrizAmenazaController;
use App\Http\Controllers\MatrizOportunidadController;
use App\Http\Controllers\MatrizRiesgoController;
use App\Http\Controllers\ClasificacionRiesgoController;
use App\Http\Controllers\VersionTablasAndroidController;
use App\Http\Controllers\RecepcionEmpleadoController;
use App\Http\Controllers\GenerarZipController;
use App\Http\Controllers\limitesCrmController;
use App\Models\SeguimientoCrm;
use App\Http\Controllers\enviarCorreoDDController;
use App\Http\Controllers\IndicadoresDDController;
use App\Http\Controllers\RolesUsuariosInternosController;
use App\Http\Controllers\HistoricoProfesionalesController;

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
  //Historico pofesionales
  Route::get('/historicoprofesionalesdd', [HistoricoProfesionalesController::class, 'index']);
  Route::get('/historicoprofesionalesdd/{cliente_id}', [HistoricoProfesionalesController::class, 'index']);

  //Roles usuarios internos
  Route::get('/rolesusuariosinternos', [RolesUsuariosInternosController::class, 'index']);
  Route::post('/rolesusuariosinternos', [RolesUsuariosInternosController::class, 'create']);
  Route::put('/rolesusuariosinternos', [RolesUsuariosInternosController::class, 'update']);
  Route::delete('/rolesusuariosinternos', [RolesUsuariosInternosController::class, 'destroy']);


  Route::post('/login', [AuthController::class, 'login']);
  Route::post('/register', [AuthController::class, 'register']);
  // Route::post('/register2', [AuthUsuarioController::class, 'register']);
  Route::post('/logout', [AuthController::class, 'logout']);
  Route::post('/refresh', [AuthController::class, 'refresh']);
  Route::get('/user-profile', [AuthController::class, 'userProfile']);

  // Usuarios
  Route::get('/allUsers', [UsuarioController::class, 'index2']);
  Route::get('/users/{cantidad}', [UsuarioController::class, 'index']);
  Route::get('/usersbyrolinterno/{rol}', [UsuarioController::class, 'byRolInterno']);
  Route::get('/users/{filtro}/{cantidad}', [UsuarioController::class, 'filtro']);
  Route::get('/userslist', [UsuarioController::class, 'userslist']);
  Route::get('/userlogued', [UsuarioController::class, 'userlogued']);
  Route::get('/userbyid/{id}', [UsuarioController::class, 'userById']);
  Route::delete('/user/{id}', [UsuarioController::class, 'destroy']);
  // Route::post('/user', [UsuarioController::class, 'create']); 
  Route::post('/user', [UsuarioController::class, 'update']);
  Route::post('/user2', [UsuarioController::class, 'update2']);
  Route::put('/updateUserVendedor/{id}', [UsuarioController::class, 'updateVendedorId']);
  // Route::get('/usuariosporcontrato', [UsuarioController::class, 'usuariosporcontrato']); 
  // Route::get('/usuariosporcontrato/{id}', [UsuarioController::class, 'usuariosporcontrato2']); 

  // Categorías menú
  Route::get('/categoriasMenu/{cantidad}', [categoriaMenuController::class, 'index']);
  Route::post('/categoriasMenu', [categoriaMenuController::class, 'create']);
  Route::post('/categoriasMenu/{id}', [categoriaMenuController::class, 'update']);
  Route::delete('/categoriasMenu/{id}', [categoriaMenuController::class, 'destroy']);
  Route::get('/categoriasMenulista', [categoriaMenuController::class, 'lista']);
  Route::post('/categoriasMenuborradomasivo', [categoriaMenuController::class, 'borradomasivo']);


  // Opciones de menú
  Route::get('/menuslista', [MenuController::class, 'index']);
  Route::post('/menus', [MenuController::class, 'create']);
  Route::post('/menus/{id}', [MenuController::class, 'update']);
  Route::delete('/menus/{id}', [MenuController::class, 'destroy']);
  Route::get('/menus', [MenuController::class, 'menubyRole']);
  Route::get('/categoriaMenu', [MenuController::class, 'categoriaMenu']);
  Route::get('/menus/{cantidad}', [MenuController::class, 'menus']);

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
  Route::get('/consultacorreo/{modulo}/{registro_id}', [RegistroCorreosController::class, 'index2']);
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
  Route::get('/exportaformularioingreso/{cadena}', [formularioIngresoExportController::class, 'export3']);

  // Estado civil
  Route::get('/estadocivil', [EstadoCivilController::class, 'index']);

  // Forma de pago
  Route::get('/formapago', [FormaPagoController::class, 'index']);

  // Banco
  Route::get('/banco', [BancoController::class, 'index']);
  Route::get('/conveniobanco', [BancoController::class, 'conveniobanco']);

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

  //Cargos de planta
  Route::get('/cargosPlanta', [CargosPlantaController::class, 'index']);

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
  Route::get('/empresascliente', [formularioDebidaDiligenciaController::class, 'empresascliente']);
  Route::get('/formulariocliente/{id}', [formularioDebidaDiligenciaController::class, 'getbyid']);
  Route::get('/clienteexist/{id}/{tipo_id}', [formularioDebidaDiligenciaController::class, 'existbyid']);
  Route::post('/formulariocliente', [formularioDebidaDiligenciaController::class, 'create']);
  Route::post('/formulariocliente/doc/{id}', [formularioDebidaDiligenciaController::class, 'store']);
  Route::post('/formulariocliente/{id}', [formularioDebidaDiligenciaController::class, 'update']);
  Route::delete('/formulariocliente/{id}', [formularioDebidaDiligenciaController::class, 'destroy']);
  Route::get('/actualizaestadofirma/{item_id}/{estado_id}/{responsable_id}', [formularioDebidaDiligenciaController::class, 'actualizaestadofirma']);
  Route::get('/versiondebidadiligencia', [formularioDebidaDiligenciaController::class, 'versionformulario']);

  Route::get('/formulariocliente/generarpdf/{id}', [formularioDebidaDiligenciaController::class, 'generarPdf']);

  Route::get('/consultaformulariocliente/{cantidad}', [formularioDebidaDiligenciaController::class, 'consultacliente']);
  Route::get('/clientesactivos', [formularioDebidaDiligenciaController::class, 'clientesactivos']);
  Route::get('/consultaformularioclientefiltro/{cadena}', [formularioDebidaDiligenciaController::class, 'filtro']);
  Route::get('/actualizaResponsableCliente/{item}/{responsable_id}/{nombre}', [formularioDebidaDiligenciaController::class, 'actualizaResponsableCliente']);
  Route::get('/contrato/{id}', [ContratoController::class, 'index']);
  Route::post('/enviarCorreoDD/{registro_id}', [enviarCorreoDDController::class, 'enviarCorreosDD']);
  Route::get('/formulariocliente/generarContratoDD/{id}', [formularioDebidaDiligenciaController::class, 'generarContrato2']);

  //Rutas para el historico de estados DD
  Route::get('/consultaHistoricoEstadosDd/{cantidad}', [HistoricoEstadosDdController::class, 'index']);
  Route::post('/consultaHistoricoEstadosDd/{cantidad}', [HistoricoEstadosDdController::class, 'filtrarEstados']);
  Route::post('/excelHistoricoEstadosDd', [HistoricoEstadosDdController::class, 'exportExcel']);
  Route::delete('/deleteAllHistoricoDD', [HistoricoEstadosDdController::class, 'deleteAll']);

  //Rutas para los indicadores DD
  Route::get('/numeroRadicadosMes/{anio}', [IndicadoresDDController::class, 'numeroRadicadosMes']);
  Route::get('/tipoDeOperacionMes/{anio}', [IndicadoresDDController::class, 'tipoDeOperacionMes']);
  Route::get('/estadoOportunoMes/{anio}', [IndicadoresDDController::class, 'estadoOportunoMes']);

  //Api validart
  Route::get('/uploadFileValidart/{id}', [ApiFirmaElectronicaController::class, 'uploadFileValidarT']);
  Route::post('/firmaValidart/{id}', [ApiFirmaElectronicaController::class, 'firmaEstandar']);
  Route::post('/callBackFirmado', [ApiFirmaElectronicaController::class, 'callBackFirmado']);
  Route::get('/reenviarFirma/{id}', [ApiFirmaElectronicaController::class, 'reenvioFirmantes']);
  Route::get('/anularContrato/{id}', [ApiFirmaElectronicaController::class, 'anularContrato']);
  Route::get('/consultaFirmantes/{id}', [ApiFirmaElectronicaController::class, 'consultaFirmantes']);
  Route::get('/consultaProcesoFirma/{id}', [ApiFirmaElectronicaController::class, 'consultaProcesoFirma']);
  Route::get('/verContratoDD/{id}', [ApiFirmaElectronicaController::class, 'verContrato']);

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
  Route::get('/cantidadvacantestiposervicio/{anio}', [DashBoardSeleccionController::class, 'cantidadVacantesTipoServicio']);


  // Nivel de accidentalidad
  Route::get('/nivelaccidentalidad', [NivelAccidentalidadController::class, 'index']);
  Route::post('/nivelaccidentalidad/{id}', [NivelAccidentalidadController::class, 'create']);
  Route::put('/nivelaccidentalidad/{id}', [NivelAccidentalidadController::class, 'update']);
  Route::delete('/nivelaccidentalidad/{id}', [NivelAccidentalidadController::class, 'destroy']);


  // Elementos de protección personal
  Route::get('/elementospp', [ElementosPPController::class, 'index']);
  Route::post('/elementospp/{id}', [ElementosPPController::class, 'create']);
  Route::put('/elementospp/{id}', [ElementosPPController::class, 'update']);
  Route::delete('/elementospp/{id}', [ElementosPPController::class, 'destroy']);

  // Estados firma
  Route::get('/estadosfirma', [EstadosFirmaController::class, 'index']);
  Route::get('/estadosfirma2', [EstadosFirmaController::class, 'index2']);
  Route::post('/estadosfirma', [EstadosFirmaController::class, 'create']);
  Route::put('/estadosfirma/{id}', [EstadosFirmaController::class, 'update']);
  Route::delete('/estadosfirma/{id}', [EstadosFirmaController::class, 'destroy']);
  Route::get('/estadoResponsableFirma', [EstadosFirmaController::class, 'indexResponsableEstado2']);
  Route::get('/estadoResponsableFirma/{estado}', [EstadosFirmaController::class, 'indexResponsableEstado']);
  Route::get('/estadosfirma/{estado}', [EstadosFirmaController::class, 'byId']);
  Route::put('/estadosfirma', [EstadosFirmaController::class, 'cambiarOrden']);

  // Historial de cambios
  Route::get('/registrocambios', [RegistroCambioController::class, 'index']);
  Route::get('/registrocambios/{id}', [RegistroCambioController::class, 'byid']);
  Route::post('/registrocambios/{id}', [RegistroCambioController::class, 'create']);
  Route::put('/registrocambios/{id}', [RegistroCambioController::class, 'update']);
  Route::delete('/registrocambios/{id}', [RegistroCambioController::class, 'destroy']);

  // Ventana modal de selección debida diligencia
  Route::get('/selecciondd', [SeleccionModalController::class, 'index']);
  Route::get('/selecciondd/{id}', [SeleccionModalController::class, 'byid']);
  Route::post('/selecciondd', [SeleccionModalController::class, 'create']);
  Route::post('/selecciondd/{id}', [SeleccionModalController::class, 'update']);
  Route::delete('/selecciondd/{id}', [SeleccionModalController::class, 'destroy']);

  // Ventana modal de selección debida diligencia
  Route::get('/contrataciondd', [ContratacionModalController::class, 'index']);
  Route::get('/contrataciondd/{id}', [ContratacionModalController::class, 'byid']);
  Route::post('/contrataciondd', [ContratacionModalController::class, 'create']);
  Route::post('/contrataciondd/{id}', [ContratacionModalController::class, 'update']);
  Route::delete('/contrataciondd/{id}', [ContratacionModalController::class, 'destroy']);

  // Otros si
  Route::get('/otrosi', [OtroSiController::class, 'index']);

  Route::get('/laboratorios', [CiudadLaboratorioController::class, 'index']);
  Route::get('/laboratorios/{id}', [CiudadLaboratorioController::class, 'byid']);


  Route::get('/interaccion', [AtencionInteraccionController::class, 'index']);
  Route::get('/procesos', [ProcesosController::class, 'index']);
  Route::get('/sede', [SedeController::class, 'index']);

  Route::get('/interaccioncliente', [ClienteInteraccionController::class, 'index']);
  Route::get('/interaccioncliente/{id}', [ClienteInteraccionController::class, 'byid']);
  Route::post('/interaccioncliente', [ClienteInteraccionController::class, 'create']);

  Route::get('/archivosingreso', [ArchivosFormularioIngresoController::class, 'index']);
  Route::get('/afp', [AfpFormularioIngresoController::class, 'index']);

  Route::get('/formularioingreso/{cantidad}', [formularioGestionIngresoController::class, 'index']);
  Route::post('/formularioIngreso/filtrofechaingreso/{cantidad}', [formularioGestionIngresoController::class, 'filtroFechaIngreso']);
  Route::get('/formularioingresobyid/{id}', [formularioGestionIngresoController::class, 'byid']);
  Route::post('/formularioingreso', [formularioGestionIngresoController::class, 'create']);
  Route::post('/formularioingresopendientes', [formularioGestionIngresoController::class, 'pendientes']);
  Route::get('/formularioingresopendientes/{cantidad}', [formularioGestionIngresoController::class, 'pendientes2']);
  Route::post('/formularioingreso/{id}', [formularioGestionIngresoController::class, 'update']);
  Route::post('/formularioingresodoc', [formularioGestionIngresoController::class, 'store']);
  Route::delete('/formularioingreso/{id}', [formularioGestionIngresoController::class, 'destroy']);
  Route::get('/formularioingresofiltro/{cadena}/{cantidad}', [formularioGestionIngresoController::class, 'filtro']);
  Route::post('/formularioingresopendientesborradomasivo', [formularioGestionIngresoController::class, 'borradomasivo']);
  Route::get('/buscardocumentoformularioi/{documento}', [formularioGestionIngresoController::class, 'buscardocumentoformularioi']);
  Route::get('/buscardocumentolistai/{documento}', [formularioGestionIngresoController::class, 'buscardocumentolistai']);
  Route::delete('/eliminararchivosingreso/{item}/{id}', [formularioGestionIngresoController::class, 'eliminararchivo']);
  Route::post('/asignacionmasivaformularioingreso/{id_estado}/{id_encargado}', [formularioGestionIngresoController::class, 'asignacionmasiva']);

  Route::get('/observacionestado', [ObservacionEstadoFormIngresoController::class, 'index']);
  Route::get('/limitesCrm', [limitesCrmController::class, 'getLimitesCrm']);
  Route::get('/recortarObservacion', [SeguimientoCrmController::class, 'recortarObservacion']);

  Route::get('/estadosingresos', [estadosIngresoController::class, 'index']);
  Route::get('/actualizaestadoingreso/{item}/{estado}', [formularioGestionIngresoController::class, 'actualizaestadoingreso']);
  Route::get('/actualizaResponsableingreso/{item}/{responsable_id}/{nombre}', [formularioGestionIngresoController::class, 'actualizaResponsableingreso']);
  Route::get('/responsableingresos/{estado}', [formularioGestionIngresoController::class, 'responsableingresos']);
  Route::get('/gestioningresospdf/{modulo}/{id}/{id_btn}', [formularioGestionIngresoController::class, 'gestioningresospdf']);
  Route::get('/consultaseguimiento/{id}', [formularioGestionIngresoController::class, 'consultaseguimiento']);

  Route::get('/consulta_id_trump/{id}', [formularioGestionIngresoController::class, 'consulta_id_trump']);


  Route::get('/tiposserviofi', [FormularioIngresoTipoServicioController::class, 'index']);

  Route::get('/solicitantecrm', [SolicitanteCrmController::class, 'index']);
  Route::get('/estadocirrecrm', [EstadoCierreCrmController::class, 'index']);
  Route::get('/pqrsf', [PqrsfCRMController::class, 'index']);

  Route::get('/seguimientocrm/{cantidad}', [SeguimientoCrmController::class, 'index']);
  Route::get('/seguimientocrmbyid/{id}', [SeguimientoCrmController::class, 'byid']);
  Route::post('/seguimientocrm', [SeguimientoCrmController::class, 'create']);
  Route::post('/seguimientocrm2', [SeguimientoCrmController::class, 'createandroid']);
  Route::post('/seguimientocrm/{id}', [SeguimientoCrmController::class, 'update']);
  Route::get('/seguimientocrmfiltro/{cadena}', [SeguimientoCrmController::class, 'filtro']);
  Route::post('/seguimientocrmpendientes', [SeguimientoCrmController::class, 'pendientes']);
  Route::get('/seguimientocrmpendientes/{cantidad}', [SeguimientoCrmController::class, 'pendientes2']);
  Route::post('/seguimientocrmpendientesborradomasivo', [SeguimientoCrmController::class, 'borradomasivo']);
  Route::delete('/eliminararevidencia/{item}/{id}', [SeguimientoCrmController::class, 'eliminararchivo']);
  Route::put('/seguimientocrmupdateevidencia/{id}', [SeguimientoCrmController::class, 'updateEvidencia']);
  Route::post('/seguimientocrmpdf/{id}/{btnId}', [SeguimientoCrmController::class, 'generarPdfCrm']);
  Route::get('/seguimientocrmpdf/{id}/{btnId}', [SeguimientoCrmController::class, 'generarPdfCrm']);
  // Route::delete('/seguimientocrm', [SeguimientoCrmController::class, 'destroy']);
  Route::delete('/seguimientocrmbyid/{id}', [SeguimientoCrmController::class, 'destroy']);
  Route::get('/compromisosGenerales', [SeguimientoCrmController::class, 'getAllCompromisos']);
  Route::delete('/compromisosGenerales/{id}', [SeguimientoCrmController::class, 'getAllCompromisos']);
  Route::get('/verEvidencia/{id}', [SeguimientoCrmController::class, 'verEvidencia']);
  Route::get('/excelCrmPqrsf', [SeguimientoCrmController::class, 'exportarExcelCrm']);

  //Rutas para el dashBoard de CRM 
  Route::get('/filtroCRM/{anio}', [FiltroCrmController::class, 'getRadicadosMes']);
  Route::get('/filtroCRMMedios/{anio}', [FiltroCrmController::class, 'getRadicadosByMedio']);
  /*   Route::get('/filtroCRMCompromisos/{cedula}', [FiltroCrmController::class, 'getCompromisosByMes']); */

  //Rutas para el formulario publico de recepcion de empleados
  Route::get('/recepcionEmpleado', [RecepcionEmpleadoController::class, 'index']);
  Route::post('/recepcionEmpleado', [RecepcionEmpleadoController::class, 'create']);
  Route::put('/recepcionEmpleado/{cod_emp}', [RecepcionEmpleadoController::class, 'updateByCodEmp']);
  Route::get('/recepcionEmpleado/{cod_emp}', [RecepcionEmpleadoController::class, 'searchByCodEmp']);
  Route::get('/paisesFormularioEmpleado', [PaisesFormualrioEmpleadoController::class, 'index']);
  Route::get('/ciudadesFormularioEmpleado', [CiudadesFormularioEmpleadoController::class, 'index']);
  Route::get('/ciudadesFormularioEmpleado/{codPai}/{codDep}', [CiudadesFormularioEmpleadoController::class, 'byCodDep']);
  Route::get('/departamentosFormularioEmpleado/{codPai}', [DepartamentosFormularioEmpleadoController::class, 'byCodPai']);
  Route::get('/tipoIdFormularioEmpleado', [TipoIdFormularioEmpleadoController::class, 'index']);
  Route::get('/bancosFormularioEmpleado', [BancosFormularioEmpleadoController::class, 'index']);
  Route::get('/nivelAcademicoFormEmpleado', [NivelAcademicoFormEmpleadoController::class, 'index']);
  Route::get('/grupoEtnicoEmpleado', [GrupoEtnicoFormEmpleadoController::class, 'index']);
  Route::get('/familiaresFormularioEmpleado', [FamiliaresFormEmpleadoController::class, 'index']);

  //ruta para generar el archivo zip de seiya
  Route::get('/descargarZip/{idRadicado}/{idCliente}', [GenerarZipController::class, 'descargarArchivosById']);





  //Indicadores SEIYA
  Route::get('/ordenserviciochar/{anio}', [IndicadoresSeyaController::class, 'ordenservicio']);
  Route::get('/resgistrosporestado', [IndicadoresSeyaController::class, 'resgistrosporestado']);
  Route::get('/registrosporresponsable', [IndicadoresSeyaController::class, 'registrosporresponsable']);
  Route::get('/estadosapilados', [IndicadoresSeyaController::class, 'estadosapilados']);
  Route::get('/vacantesOcupadasTipoServicio', [IndicadoresSeyaController::class, 'vacantesOcupadasTipoServicio']);
  Route::get('/vacantesocupadas/{anio}', [IndicadoresSeyaController::class, 'vacantesocupadas']);
  Route::get('/ingresoempledosmes/{anio}', [IndicadoresSeyaController::class, 'ingresoempledosmes']);


  Route::post('/borrar_nc/{id}', [formularioGestionIngresoController::class, 'borrar_nc']);
  Route::get('/hora', [formularioGestionIngresoController::class, 'hora']);

  Route::post('/buscarcedula', [formularioGestionIngresoController::class, 'buscarcedula']);

  Route::get('/actualizacionprogramada', [CuentaRegresivaController::class, 'index']);
  Route::post('/actualizacionprogramada', [CuentaRegresivaController::class, 'create']);
  Route::post('/actualizacionprogramada/{id}', [CuentaRegresivaController::class, 'update']);
  Route::get('/ocultacontador', [CuentaRegresivaController::class, 'ocultacontador']);

  Route::get('/modalprincipal', [ModalPrincipalController::class, 'index']);
  Route::post('/modalprincipal', [ModalPrincipalController::class, 'create']);
  Route::post('/modalprincipal/{id}', [ModalPrincipalController::class, 'update']);
  Route::post('/showmodalprincipal/{id}', [ModalPrincipalController::class, 'updatevisibility']);


  Route::get('/nivelimpacto', [RiesgosNivelImpactoController::class, 'index']);
  Route::get('/nivelprobabilidad', [RiesgosNivelProbabilidadController::class, 'index']);
  Route::get('/tiposproceso', [RiesgosTiposProcesoController::class, 'index']);
  Route::get('/nombresproceso', [RiesgosNombresProcesoController::class, 'index']);
  Route::get('/metodosidentificacion', [RiesgosMetodosIdentificacionController::class, 'index']);
  Route::get('/factores', [RiesgoFactoresController::class, 'index']);
  Route::get('/seguimientos', [RiesgoSeguimientosController::class, 'index']);

  Route::get('/documentosregistrados', [RiesgoDocumentosRegistradosController::class, 'index']);
  Route::get('/clasescontrol', [RiesgoClasesControlController::class, 'index']);
  Route::get('/frecuenciascontrol', [RiesgoFrecuenciasControlController::class, 'index']);
  Route::get('/tiposcontrol', [RiesgoTiposControlController::class, 'index']);
  Route::get('/existeevidencias', [RiesgoExisteEvidenciasController::class, 'index']);
  Route::get('/ejecucioneseficaces', [RiesgoEjecucionesEficacesController::class, 'index']);

  Route::get('/riesgoscontrol', [RiesgoControlController::class, 'index']);
  Route::get('/matrizamenazas', [MatrizAmenazaController::class, 'index']);
  Route::get('/matrizoportunidades', [MatrizOportunidadController::class, 'index']);
  Route::get('/matrizriesgo/{cantidad}', [MatrizRiesgoController::class, 'index']);
  Route::get('/matrizriesgobyid/{id}', [MatrizRiesgoController::class, 'byid']);
  Route::post('/matrizriesgo', [MatrizRiesgoController::class, 'create']);
  Route::post('/matrizriesgo/{id}', [MatrizRiesgoController::class, 'update']);
  Route::post('/matrizriesgo/doc/{id}', [MatrizRiesgoController::class, 'store']);
  Route::get('/matrizriesgofiltro/{cadena}', [MatrizRiesgoController::class, 'riesgosfiltro']);
  Route::get('/exportamatrizriesgo/{cadena}', [MatrizRiesgoController::class, 'exportamatrizriesgo']);
  Route::get('/buscarradicado/{radicado}', [MatrizRiesgoController::class, 'buscarradicado']);
  Route::get('/lideres', [MatrizRiesgoController::class, 'lideres']);
  Route::get('/clasificacionesriesgos', [ClasificacionRiesgoController::class, 'index']);

  //Actualizar riesgo
  Route::get('/updateRiesgoAlto', [MatrizRiesgoController::class, 'updateRiesgo']);

  Route::get('/tablasandroid', [VersionTablasAndroidController::class, 'index']);
  Route::get('/tablasandroid2', [VersionTablasAndroidController::class, 'index2']);
  Route::get('/tablasandroid_usr_app_tablas_android', [VersionTablasAndroidController::class, 'usr_app_tablas_android']);
  Route::get('/tablasandroid_usr_app_sedes_saitemp', [VersionTablasAndroidController::class, 'usr_app_sedes_saitemp']);
  Route::get('/tablasandroid_usr_app_procesos', [VersionTablasAndroidController::class, 'usr_app_procesos']);
  Route::get('/tablasandroid_usr_app_solicitante_crm', [VersionTablasAndroidController::class, 'usr_app_solicitante_crm']);
  Route::get('/tablasandroid_usr_app_atencion_interacion', [VersionTablasAndroidController::class, 'usr_app_atencion_interacion']);
  Route::get('/tablasandroid_usr_app_usuarios_responsable', [VersionTablasAndroidController::class, 'usr_app_usuarios_responsable']);
  Route::get('/tablasandroid_usr_app_usuarios_visitante', [VersionTablasAndroidController::class, 'usr_app_usuarios_visitante']);
  Route::get('/tablasandroid_usr_app_cargos_crm', [VersionTablasAndroidController::class, 'usr_app_cargos_crm']);
  Route::get('/tablasandroid_usr_app_estado_cierre_crm', [VersionTablasAndroidController::class, 'usr_app_estado_cierre_crm']);
  Route::get('/tablasandroid_usr_app_estado_compromiso_crm', [VersionTablasAndroidController::class, 'usr_app_estado_compromiso_crm']);
  Route::get('/tablasandroid_usr_app_pqrsf_crm', [VersionTablasAndroidController::class, 'usr_app_pqrsf_crm']);
  Route::get('/tablasandroid_usr_app_cliente_debida_diligencia', [VersionTablasAndroidController::class, 'usr_app_clientes']);

  Route::get('/clear-cache', function () {
    echo Artisan::call('config:clear');
    echo Artisan::call('config:cache');
    echo Artisan::call('cache:clear');
    echo Artisan::call('route:clear');
  });
  Route::post('/pruebaCorreo', [EnvioCorreoController::class, 'correoPrueba']);


  // Route::get('/otrosi/{id}', [OtroSiController::class, 'byid']);
  // Route::post('/otrosi', [OtroSiController::class, 'create']);
  // Route::post('/otrosi/{id}', [OtroSiController::class, 'update']);
  // Route::delete('/otrosi/{id}', [OtroSiController::class, 'destroy']);

  // estos endpoint se usan para asignar a un empleado el lider correspondiente
  // Route::get('/examen/{cedula}', [ExamenPruebaController::class, 'examen']);
  // Route::get('/examenprueba', [ExamenPruebaController::class, 'create']);
});
