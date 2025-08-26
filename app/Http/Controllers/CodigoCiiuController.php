<?php

namespace App\Http\Controllers;

use App\Models\CodigoCiiu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CodigoCiiuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    //  public function index()
    // {
    //     $myarray =  [
    //         ['codigo_ciiu' => '1312', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1393', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1410', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '4541', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4542', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4610', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4620', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4631', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4641', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4642', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4643', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4644', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4645', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4649', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4651', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '4659', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4661', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4663', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4664', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4669', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4690', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4711', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4719', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4722', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4723', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4724', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4729', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4732', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4741', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4742', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4751', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4752', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4753', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4754', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4755', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4759', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4761', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4762', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4769', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4771', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4772', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4773', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4774', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4775', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4781', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4782', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4789', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4791', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4792', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4799', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5613', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5820', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6201', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6202', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6209', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6311', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6312', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6399', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6411', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6412', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6421', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6422', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6423', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6424', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6431', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6432', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6491', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6492', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6493', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6494', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6495', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6499', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6511', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6512', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6513', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6514', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6611', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6612', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6613', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6614', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6615', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6619', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6630', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6810', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6820', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6910', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '6920', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '7010', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '7020', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '7110', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '7210', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '7310', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '7320', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '7490', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '7722', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '7729', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '7740', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '7810', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '7911', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '7912', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '7990', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8220', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8230', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '8291', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '8299', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '8411', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8412', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8413', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8414', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '8415', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8421', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8513', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8521', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8522', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8523', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8541', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8542', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8543', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8544', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8551', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8553', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8559', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8560', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8810', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8890', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '9001', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '9003', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '9004', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '9101', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '9102', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '9200', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '9411', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '9412', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '9491', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '9499', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '9524', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '9529', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '9602', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '9609', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '9609', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '9700', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '111', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '112', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '113', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '114', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '115', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '119', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '121', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '122', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '123', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '126', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '127', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '128', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '129', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '130', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '141', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '142', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '143', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '144', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '145', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '149', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '150', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '161', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '162', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '163', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '164', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '170', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '210', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '220', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '230', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '240', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '240', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '321', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '322', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '1011', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1020', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1040', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1051', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1052', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1081', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1089', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1313', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1391', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1392', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1399', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1420', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1430', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1512', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1521', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1522', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1690', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1702', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1709', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1811', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1812', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1820', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '2013', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2023', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2029', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2219', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2599', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2670', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2680', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '3110', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '3220', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '3230', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '3290', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '3311', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '3312', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '3313', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '3319', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '3320', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '4330', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4511', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4512', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4632', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4652', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '4653', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4662', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4721', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5210', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5221', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5511', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5512', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5513', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5514', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5519', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5520', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5530', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5590', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5612', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '5630', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5911', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5912', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5913', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5914', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5920', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6010', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6020', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6130', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6190', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6521', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6522', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6531', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6532', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6621', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6629', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '7410', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '7420', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '7490', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '7500', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '7710', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '7721', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '7730', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8010', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8110', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '8121', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '8129', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '8130', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8211', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '8219', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '8291', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8292', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '8299', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '8430', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8511', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8512', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8530', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8552', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '8621', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8622', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8710', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8720', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8730', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8790', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '9002', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '9005', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '9006', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '9312', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '9329', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '9511', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '9512', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '9521', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '9522', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '9523', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '9529', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '9603', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '9609', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '125', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '891', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '892', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '1012', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1051', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '1061', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1062', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1072', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1082', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1083', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1084', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '1090', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1101', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1102', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1104', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1200', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1311', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1394', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1410', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '1513', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1523', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1610', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1630', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1640', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1922', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '2011', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2014', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2022', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2030', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2100', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2229', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2310', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2394', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2396', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2399', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2421', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2511', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2520', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2591', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2592', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2593', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2610', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2620', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2630', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2640', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2651', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2652', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2660', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2711', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2712', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2732', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2740', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2750', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2790', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2817', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2818', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2819', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2821', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2822', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2826', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2829', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2920', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '3091', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '3092', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '3099', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '3120', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '3210', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '3240', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '3250', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '3290', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '3311', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '3314', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '3530', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '3600', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '3811', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '3821', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '3830', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '4321', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4322', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4520', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '4530', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4665', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4731', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4923', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5224', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5611', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5612', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5619', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '5621', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5629', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5811', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '5812', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '5813', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '5819', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '5920', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '6110', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '6120', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '7220', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '7410', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '7820', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '7830', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '8130', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '8292', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '8610', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8691', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8692', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '9007', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '9008', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '9103', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '9311', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '9319', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '9319', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '9321', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '9420', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '9492', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '9601', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '9900', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '124', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '161', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '311', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '312', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '729', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '899', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1011', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '1030', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '1030', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1063', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1071', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1103', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1511', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1610', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '1620', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1701', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '1921', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2012', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2211', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2212', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2221', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2391', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2392', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2393', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2395', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2429', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2599', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '2720', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2731', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2811', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2815', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2825', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2910', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2930', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '3020', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '3030', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '3290', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '3312', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '3315', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '3514', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '3520', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '3700', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '3812', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4111', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4290', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4520', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4664', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '4669', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '4759', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '4911', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4912', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4921', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4922', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5011', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5012', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5021', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5022', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5111', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '5112', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '5121', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '5122', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '5229', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5310', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '5320', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '6391', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '8010', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '8020', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '8699', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '9603', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '161', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '510', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '520', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '610', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '620', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '710', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '721', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '722', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '723', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '729', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '811', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '812', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '820', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '891', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '899', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '910', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '990', 'sector_economico' => '2'],
    //         ['codigo_ciiu' => '1630', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '1910', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2021', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2410', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2431', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2432', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2512', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '2513', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2812', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2813', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2814', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2816', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2823', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '2824', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '3011', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '3012', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '3040', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '3320', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '3511', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '3512', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '3513', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '3822', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '3900', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4112', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4210', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4210', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '4220', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4311', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4312', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4329', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4330', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '4390', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '4390', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '4610', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '4930', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5210', 'sector_economico' => '3'],
    //         ['codigo_ciiu' => '5222', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '5223', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '6810', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '7110', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '7120', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '8030', 'sector_economico' => '1'],
    //         ['codigo_ciiu' => '8422', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8423', 'sector_economico' => '4'],
    //         ['codigo_ciiu' => '8424', 'sector_economico' => '4']
    //     ];
    //     $result = CodigoCiiu::select('id','codigo', 'sector_economico_id')->get();

    //     // Recorremos el resultado y actualizamos los valores en función de las coincidencias
    //     foreach ($result as $item) {
    //         foreach ($myarray as $reference) {
    //             if ($item->codigo === $reference['codigo_ciiu']) {
    //                 $item->sector_economico_id = $reference['sector_economico']; // Actualizar sector económico
    //                 $item->save(); // Guardar los cambios en la base de datos
    //                 break; // Salimos del bucle interno si ya hay coincidencia
    //             }
    //         }
    //     }

    //     return response()->json([
    //         'message' => 'Registros actualizados correctamente.',
    //         'updated_records' => $result
    //     ]);
    // }

    public function index()
    {
        $result = CodigoCiiu::select(
            'id',
            'codigo',
            'descripcion',
            'sector_economico_id',
        )->get();
        return response()->json($result);
    }

    public function tabla($cantidad)
    {
        $result = CodigoCiiu::join('usr_app_sector_economico as se', 'se.id', 'usr_app_codigos_ciiu.sector_economico_id')
            ->select(
                'usr_app_codigos_ciiu.id',
                'usr_app_codigos_ciiu.codigo',
                'usr_app_codigos_ciiu.descripcion',
                'usr_app_codigos_ciiu.sector_economico_id',
                'se.nombre as sector_economico',
            )->paginate($cantidad);
        return response()->json($result);
    }


    public function byid($id)
    {
        $result = CodigoCiiu::leftJoin('usr_app_sector_economico as se', 'se.id', 'usr_app_codigos_ciiu.sector_economico_id')
            ->where('usr_app_codigos_ciiu.codigo', '=', $id)
            ->select(
                'usr_app_codigos_ciiu.id',
                'usr_app_codigos_ciiu.codigo',
                'usr_app_codigos_ciiu.descripcion',
                'usr_app_codigos_ciiu.sector_economico_id',
                'se.nombre as sector_economico',
            )->paginate(10);
        return response()->json($result);
    }

    public function codigo_sector(Request $request)
    {
        foreach ($request->codigos as $item) {
            try {
                DB::beginTransaction();
                $result = CodigoCiiu::find($item['id']);
                $result->sector_economico_id = $request->sector['id'];
                $result->save();
                DB::commit();
            } catch (\Exception $e) {
                DB::rollback();
                return response()->json(['status' => 'error', 'message' => 'Error al guardar el registro.']);
            }
            return response()->json(['status' => 'success', 'message' => 'Registro guardado de manera exitosa.']);
        }
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}