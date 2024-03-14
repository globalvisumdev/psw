<?php require('estructura/header.php') ?>
<link rel="stylesheet noopener" href="../../modulos/buttonProgress/buttonProgress.css">
<link rel="stylesheet noopener" href="../../modulos/toastNotification/toastNotification.css">

<title>Registro de Actividades</title>
</head>
<style>
    .btnBuscar{
        display: flex;
        align-items: flex-end;
    }

    .divHeader {
        display: flex;
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
            <div class="header-section animate__animated" id="headerTitle">
                <h2 class="title">Registro de <span>Acciones</span></h2>
            </div>
            <div class="circCont">
                <button class="circle boxShadow" data-animation="fadeOut" data-remove="3000"
                    onclick="redirect()"></button>
            </div>
        </div>

        <form class="row g-2  animate__animated animate__fadeIn" id="formularioReporte">     
            <div class="col-md-6 position-relative" id="grupoFechaDesde">
                <label for="date" class="form-label">Fecha Desde:</label>
                <input type="date" class="form-control" id="fecha_desde" required="">
            </div>
            <div class="col-md-6 position-relative" id="grupoFechaHasta">
                <label for="date" class="form-label">Fecha Hasta:</label>
                <input type="date" class="form-control" id="fecha_hasta" required="">
            </div>
            <div class="buttonProgress">
                <div class="content">
                    <i class="bx bx-cloud-download"></i>
                    <span class="button-text" onclick="validarFormulario('formularioReporte', generarReporte)">Generar</span>
                </div>
            </div>
        </form>
    </div>

    <div class="container hidden animate__animated " id="contenidoReporte">
        <div class="divHeader container">
            <div class="header-section animate__animated" id="headerTitle">
                <h2 class="title">Registro de <span>Acciones</span></h2>
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
                        <th scope="col">Fecha</th>
                        <th scope="col">Ip</th>
                        <th scope="col">Usuario</th>
                        <th scope="col">Id</th>
                        <th scope="col">Cliente</th>
                        <th scope="col">Actividad</th>
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

    
    let formularios = document.querySelectorAll("#formularioReporte");

    formularios.forEach(formulario => {
        formulario.addEventListener("submit", (e) => {
            e.preventDefault();
        })
    });

    function generarReporte(animation=true){
        let inputs = document.querySelectorAll('#formularioReporte input');

        let bodyValue = `cmd=generarReporte`

        inputs.forEach(input => {
            bodyValue += `&form_viewLogForm[${input.id}]=${input.value}`
        });


        fetch("viewLog_cmd.php",{
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
            if (res.ok) {

                let datosTabla = ["fechayhora","ip","username","accountid", "cliente_nombre","description"]
                let objPermisos = res.data;

                llenarDatosTabla("bodyTable", objPermisos, datosTabla);
                
                if(animation){
                    
                    buttonProgress();
                    setTimeout(()=>{

                        let contenido = document.getElementById("contenido")
                        let contenidoReporte = document.getElementById("contenidoReporte")

                        contenido.classList.replace('animate__fadeInDown','animate__fadeOutUp');

                        setTimeout(()=>{
                            contenido.classList.add("hidden");
                            contenidoReporte.classList.remove("animate__fadeOutUp");
                            contenidoReporte.classList.remove("hidden");
                            contenidoReporte.classList.add("animate__fadeInDown");
                        },800)

                    }, 4500)
                }
                
            }
        })
        .catch(e => {
            toastNotification({ok: false, errorMsg: "Ha ocurrido un error de conexión."});
        })
    }

    function ocultarTabla(){
        let contenido = document.getElementById("contenido");
        let contenidoReporte = document.getElementById("contenidoReporte");

        contenidoReporte.classList.replace("animate__fadeInDown", "animate__fadeOutUp");
        
        setTimeout(()=>{
            contenidoReporte.classList.add("hidden");
            contenido.classList.remove("animate__fadeOutUp");
            contenido.classList.remove("hidden");
            contenido.classList.add("animate__fadeInDown");
        },800)
    }


    function redirect(pagina = 'usuarioServices.html') {
        let contenido = document.getElementById("contenido");
        contenido.classList.replace('animate__fadeInDown','animate__fadeOutUp');

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

