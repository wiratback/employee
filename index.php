<?php
require 'mysql/config.php';
if (isset($_POST['act'])) {
    $act = $_POST['act'];
    $empid = isset($_POST['empid']) ? $_POST['empid'] : "";
    $empname = isset($_POST['empname']) ? $_POST['empname'] : "";
    $empdep = isset($_POST['empdep']) ? $_POST['empdep'] : 1;
    $empnote = isset($_POST['empnote']) ? $_POST['empnote'] : "";

    if ($act == 1) {
        $statement = $conn->prepare("INSERT INTO employees(empid, empname, empdep, empnote) VALUES(?,?,?,?)");
        $param = array($empid, $empname, $empdep, $empnote);
    } else if ($act == 2) {
        $statement = $conn->prepare("UPDATE employees SET empname=?, empdep=?, empnote=? WHERE empid=?");
        $param = array($empname, $empdep, $empnote, $empid);
    } else {
        unset($statement);
    }
} else if (isset($_POST['emprmid'])) {
    $empid = $_POST['emprmid'];
    $statement = $conn->prepare("DELETE FROM employees WHERE empid=?");
    $param = array($empid);
}

if (isset($statement)) {
    try {

        $statement->execute($param);
        if (isset($_FILES['empimg'])) {
            move_uploaded_file($_FILES['empimg']['tmp_name'], "./photos/" . $empid . ".jpg");
        }

        if (isset($_POST['emprmid']) && (int)file_exists("./photos/" . $empid . ".jpg")) {
            unlink("./photos/" . $empid . ".jpg");
        }
        $alert = "Complete";
    } catch (Exception $ex) {
        $alert = "Fail";
    }
    echo "<script>alert('$alert');</script>";
    echo "<script>window.location.replace('index.php');</script>";
}

$statement = $conn->prepare("SELECT * FROM employees");
$statement->execute();
$employees = array();
while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {

    $empimg = ((int)file_exists("./photos/" . $row['empid'] . ".jpg")) ? "./photos/" . $row['empid'] . ".jpg" : "./photos/null.jpg";
    $data = array(
        "act" => 2,
        "empid" => $row['empid'],
        "empname" => $row['empname'],
        "empdep" => (int)$row['empdep'],
        "empnote" => $row['empnote'],
        "empimg" => $empimg
    );
    array_push($employees, $data);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบฐานข้อมูลพนักงาน</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <link rel="stylesheet" href="//cdn.datatables.net/1.10.22/css/jquery.dataTables.min.css">
</head>

<body>
    <nav class="navbar navbar-expand-sm bg-info navbar-dark">
        <a class="navbar-brand" href="#">Employee</a>

    </nav>
    <div id="app">
        <div class="container-fluid">
            <h1>ระบบฐานข้อมูลพนักงาน</h1>
            <p>Employee system.</p>
            <button type="button" class="btn btn-primary" @click="formfn(null)">Add new employee</button>
            <br><br>
            <table id="employeetable" class="display" style="width:100%">
                <thead>
                    <tr>
                        <th>รหัส</th>
                        <th >ชื่อ-สกุล</th>
                        <th class="text-center">แผนก</th>
                        <th class="text-center">จัดการ</th>
                    </tr>
                </thead>

                <tbody>
                    <tr v-for="(item,index) in employees" :index="index">
                        <td>{{item.empid}}</td>
                        <td>{{item.empname}}</td>
                        <td class="text-center">{{departments[item.empdep]}}</td>
                        <td class="text-center"><button type="button" class="btn btn-info" @click="formfn(index)">Info</button></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Form Modal -->
        <div class="modal fade" id="formModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">Employee form</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <!-- SELECT `empid`, `empname`, `empdep`, `empnote` FROM `employees`  -->
                    <form enctype="multipart/form-data" method="POST">
                        <input type="hidden" name="act" v-model="employee.act">
                        <!-- Modal body -->
                        <div class="modal-body">
                            <div class="text-center">
                                <img :src="employee.empimg" height="100px">
                            </div>
                            <div class="form-group">
                                <label for="empimg">รูป</label>
                                <input type="file" class="form-control" id="empimg" name="empimg" accept="image/*">
                            </div>

                            <div class="form-group">
                                <label for="empid">รหัส</label>
                                <input v-if="employee.act==1" type="text" class="form-control" id="empid" name="empid" v-model="employee.empid" require>
                                <input v-else type="text" class="form-control" id="empid" name="empid" v-model="employee.empid" readonly>
                            </div>

                            <div class="form-group">
                                <label for="empname">ชื่อ-สกุล</label>
                                <input type="text" class="form-control" id="empname" name="empname" v-model="employee.empname" require>
                            </div>

                            <div class="form-group">
                                <label for="empdep">แผนก</label>
                                <select class="form-control" name="empdep" id="empdep" v-model="employee.empdep">
                                    <option v-for="(item,index) in departments" :index="index" v-if="index>0" :value="index">
                                        {{item}}
                                    </option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="empnote">บันทึก</label>
                                <textarea class="form-control" name="empnote" id="empnote " rows="5" v-model="employee.empnote">

                                </textarea>
                            </div>

                        </div>

                        <!-- Modal footer -->
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Save</button>
                            <button v-if="employee.act==1" type="button" class="btn btn-danger" disabled>Remove</button>
                            <button v-else type="button" class="btn btn-danger" @click="removefn">Remove</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

        <!-- Remove Modal -->
        <div class="modal fade" id="removeModal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h4 class="modal-title">Remove</h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <form method="post">
                        <input type="hidden" name="emprmid" v-model="employee.empid">
                        <!-- Modal body -->
                        <div class="modal-body">
                            ยืนยันการลบข้อมูลของพนักงาน<br>
                            รหัส : {{employee.empid}}<br>
                            ชื่อ : {{employee.empname}}<br>
                        </div>

                        <!-- Modal footer -->
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-danger" @click="removefn">Remove</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>

    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.min.js" integrity="sha384-w1Q4orYjBQndcko6MimVbzY0tgp4pWB4lZ7lr30WKz0vr/aWKhXdBNmNb5D92v7s" crossorigin="anonymous"></script>
    <script src="//cdn.datatables.net/1.10.22/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.12/dist/vue.js"></script>
    <script>
        var app = new Vue({
            el: '#app',
            data: {
                employees: <?php echo json_encode($employees, JSON_UNESCAPED_UNICODE); ?>,
                employee: {},
                departments: <?php echo json_encode($departments, JSON_UNESCAPED_UNICODE); ?>
            },
            methods: {
                initfn() {
                    $('#employeetable').DataTable();
                },

                formfn(v1) {
                    if (v1 != null) {
                        this.employee = this.employees[v1];
                    } else {
                        this.employee = {
                            act: 1,
                            empdep: 1,
                            empid: null,
                            empimg: "./photos/null.jpg",
                            empname: null,
                            empnote: null
                        };
                    }
                    $('#removeModal').modal('hide');
                    $('#formModal').modal('show');
                },

                removefn() {
                    $('#formModal').modal('hide');
                    $('#removeModal').modal('show');
                },

            },
            mounted() {
                this.initfn();


            },
        });
    </script>
</body>

</html>