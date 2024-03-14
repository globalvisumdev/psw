<?php require('estructura/header.php') ?>
<link rel="stylesheet noopener" href="../../modulos/buttonProgress/buttonProgress.css">
<link rel="stylesheet noopener" href="../../modulos/toastNotification/toastNotification.css">


<title>Permisos Eventuales por usuario</title>
</head>
<style>
    .divHeader {
        display: flex;
    }
    #tituloFormularioReporte{
        margin-top: 15px;
    }
</style>

<body>
    <div class="toastDiv hidden">
        <div class="toast-content">
            <i id="logoOperacion" class="fa-solid check"></i>
            <div class="message">
                <span class="text text-1" id="estadoOperacion"></span>
                <span class="text text-2" id="mensajeOperacion"></span>
            </div>
        </div>
        <i class="fa-solid fa-xmark closeIcon"></i>
        <div class="progress"></div>
    </div>
        
    <div  class="container animate__animated animate__fadeInDown animate__faster" id="contenido">
        <div class="divHeader container">
            <div class="header-section">
                <h2 class="title">Permisos Eventuales por <span>usuario</span></h2>
            </div>
            <div class="circCont">
                <button class="circle boxShadow" data-animation="fadeOut" data-remove="3000"
                    onclick="redirect()"></button>
            </div>
        </div>

        <form class="row g-2" id="formulario">
            <div class="col-md-6 position-relative" >
                <label for="permiso" class="form-label">Permiso</label>
                <select class="form-select" id="permiso">
                    <option selected="" value="" disabled=""> Seleccione un permiso</option>
                </select>
            </div>
            <div class="col-md-6 position-relative" >
                <label for="Usuario" class="form-label">Usuario</label>
                <select class="form-select uppercase" id="usuario">
                    <option selected="" value="" disabled=""> Seleccione un Usuario</option>
                </select>
            </div>
            <div class="col-md-6 position-relative" >
                <label for="date" class="form-label">Fecha Inicio:</label>
                <input type="date" class="form-control" id="fdesde">
            </div>
            <div class="col-md-6 position-relative" >
                <label for="date" class="form-label">Hora Inicio:</label>
                <input type="time" class="form-control"  id="hdesde">
            </div>
            <div class="col-md-6 position-relative" >
                <label for="date" class="form-label">Fecha Fin:</label>
                <input type="date" class="form-control" id="fhasta">
            </div>
            <div class="col-md-6 position-relative" >
                <label for="date" class="form-label">Hora Fin:</label>
                <input type="time" class="form-control"  id="hhasta">
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button class="btn btn-danger"  onclick="redirect()">Cancelar</button>
                <button class="btn btn-primary" id="grabar" onclick="validarFormulario('formulario', agregarPermiso)">Grabar</button>
            </div>
        </form>
        
        <div class="header-section" id="tituloFormularioReporte">
            <h2 class="title">Generar <span>Reporte</span></h2>
        </div>

        <form class="row g-2" id="formularioReporte">
            <div class="col-md-12 position-relative">
                <select class="form-select" id="formatoVer">
                    <option value="" selected="" disabled=""> Seleccione el formato</option>
                    <option value="html"> Mostrar en pantalla</option>
                    <option value="xls"> Excel (.xls)</option>
                    <option value="csv"> Separado por comas (.csv)</option>
                </select>
            </div>
            <div class="buttonProgress" onclick="validarFormulario('formularioReporte', generarReporte)">
                <div class="content">
                    <i class="bx bx-cloud-download"></i>
                    <span class="button-text">Generar</span>
                </div>
            </div>
        </form>

    </div>

    <div class="container hidden animate__animated " id="contenidoReporte">
        <div class="divHeader container">
            <div class="header-section">
                <h2 class="title">Permisos Eventuales por <span>usuario</span></h2>
            </div>
            <div class="circCont">
                <button class="circle boxShadow" data-animation="fadeOut" data-remove="3000"
                    onclick="ocultarTabla()"></button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Usuario</th>
                        <th scope="col">Permiso</th>
                        <th scope="col">Desde</th>
                        <th scope="col">Hasta</th>
                        <th scope="col">Fecha Creación</th>
                        <th scope="col">Eliminar</th>
                    </tr>
                </thead>
                <tbody id="bodyTable">
                </tbody>
            </table>
        </div>
    </div>

    <?php require('estructura/footer.php') ?>
  <script src="../../modulos/buttonProgress/buttonProgress.js"></script>
  <script src="../../modulos/toastNotification/toastNotification.js" ></script>

  <script> 

    let formularios = document.querySelectorAll("#formulario, #formularioReporte");

    formularios.forEach(formulario => {
        formulario.addEventListener("submit", (e) => {
            e.preventDefault();
        })
    });

    function generarReporte(animation=true){

        let selectFormatoVer = document.getElementById("formatoVer");
        let formatoVer = selectFormatoVer.options[selectFormatoVer.selectedIndex].value;

        fetch("accountaction_eventual_cmd.php",{
            method:'POST',
            headers:{
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cmd=generarReporte&form_PermissionForm[formato_ver]=${formatoVer}`
        })
        .then( peticion => peticion.json() )
        .then( res=>{
            if (res.redirectPage) {
                redirect(res.redirectPage);
                 return
            }
            if (res.ok) {
                let datosTabla = [["lastname","firstname"],"aae_username_alta","actionname","aae_fecha_desde", "aae_fecha_hasta","aae_fecha_alta"]
                let objPermisos = res.data;
                let botones = {
                    btnEliminar: 'aae_id'
                }

                llenarDatosTabla("bodyTable", objPermisos, datosTabla, botones );
                
                if(animation){
                    
                    buttonProgress();
                    setTimeout(()=>{

                        let contenido = document.getElementById("contenido")
                        let contenidoReporte = document.getElementById("contenidoReporte")

                        contenido.classList.replace("animate__fadeInDown", "animate__fadeOut");

                        setTimeout(()=>{
                            contenido.classList.add("hidden");
                            contenidoReporte.classList.remove("animate__fadeOut");
                            contenidoReporte.classList.remove("hidden");
                            contenidoReporte.classList.add("animate__fadeInDown");
                        },500)

                    }, 4500)
                }
                
            }
        })
        .catch(e => {
            // toastNotification({ok: false, errorMsg: "Ha ocurrido un error de conexión."});
        })
    }
    
    function ocultarTabla(){
        let contenido = document.getElementById("contenido")
        let contenidoReporte = document.getElementById("contenidoReporte")

        contenidoReporte.classList.replace("animate__fadeInDown", "animate__fadeOut");
        
        setTimeout(()=>{
            contenidoReporte.classList.add("hidden");
            contenido.classList.remove("animate__fadeOut");
            contenido.classList.remove("hidden");
            contenido.classList.add("animate__fadeInDown");
        },500)
    }

    function cargarSelects(){

        let dataBase = ["accountsList_cmd.php", "accountaction_eventual_cmd.php"];
        let selectId = ["usuario", "permiso"];
        let idOption = ["accountid", "actionid"];
        let nameOption = [["lastname","firstname"], "actionname"];

        for (let i = 0; i < dataBase.length; i++) {

            fetch(dataBase[i],{
                method:'POST',
                headers:{
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `cmd=cargarTabla`
            })
            .then( peticion => peticion.json() )
            .then( res=>{
            if (res.redirectPage) {
                redirect(res.redirectPage);
                 return
            }
                if (res.ok) {
                    let objetoDatos = res.data;

                    cargarOpcionesSelect(selectId[i], objetoDatos, idOption[i], nameOption[i]);
                }
                else{
                    // Mensaje de error de la base de datos
                }
            })
            .catch(e => {
                toastNotification({ok: false, errorMsg: "Ha ocurrido un error de conexión."});
            })
        }

    }

    function agregarPermiso(){

        let inputs = document.querySelectorAll('#formulario input');
        let selects = document.querySelectorAll('#formulario select');
        let bodyValue= `cmd=agregarPermiso`;
        
        inputs.forEach(input => {
            bodyValue += `&form_PermissionForm[${input.id}]=${input.value}`
        });

        selects.forEach(select => {
            bodyValue += `&form_PermissionForm[${select.id}]=${select.options[select.selectedIndex].value}`
        });

        
        fetch("accountaction_eventual_cmd.php",{
            method:'POST',
            headers:{
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: bodyValue
        })
        .then( peticion => peticion.json() )
        .then( res=>{
            if (res.redirectPage) {
                redirect(res.redirectPage);
                 return
            }
            toastNotification(res);
            limpiarFormulario();
             
        })
        .catch(e => {
            toastNotification({ok: false, errorMsg: "Ha ocurrido un error de conexión."});
        })

    }

    function eliminarPermiso(idPermiso){

        fetch("accountaction_eventual_cmd.php",{
            method:'POST',
            headers:{
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cmd=eliminarPermiso&form_PermissionForm[id]=${idPermiso}`
        })
        .then( peticion => peticion.json() )
        .then( res=>{
            if (res.redirectPage) {
                redirect(res.redirectPage);
                 return
            }
            toastNotification(res);
            generarReporte(false);
        })
        .catch(e => {
            toastNotification({ok: false, errorMsg: "Ha ocurrido un error de conexión."});
        })

    }
    


    window.onload = ()=>{
        cargarSelects();
    }
    
    function redirect(pagina = 'usuarioServices.html') {
        let contenido = document.getElementById("contenido");
        contenido.classList.remove('animate__fadeInDown');
        contenido.classList.add('animate__fadeOutUp');

               setTimeout(() => {
             setTimeout(() => {
                contenido.classList.replace('animate__fadeOut','animate__fadeIn');
            }, 500);
            window.location = pagina;
        }, 600);
    }

</script> 
</body>
</html>