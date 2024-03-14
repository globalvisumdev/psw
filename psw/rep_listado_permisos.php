<?php require('estructura/header.php');
include("securityConfig.inc.php");
?>
<link rel="stylesheet noopener" href="../../modulos/buttonProgress/buttonProgress.css">
<link rel="stylesheet noopener" href="../../modulos/toastNotification/toastNotification.css">

<title>Reporte de Usuarios y Permisos</title>
</head>
<style>
    .btnGenerar{
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
            <div class="header-section">
                <h2 class="title">Reporte de Usuarios y <span>Permisos</span></h2>
            </div>
            <div class="circCont">
                <button class="circle boxShadow" data-animation="fadeOut" data-remove="3000"
                    onclick="redirect()"></button>
            </div>
        </div>
            <form class="row g-2  animate__animated animate__fadeIn" id="formularioReporte">
                <?php if (USA_MICRONAUTA) {?>
                <div class="col-md-6 position-relative" >
                    <label for="servicio" class="form-label">Servicio:</label>
                    <select class="form-select" id="servicio" required="">
                        <option selected="" disabled="" value=""> Seleccione servicio</option>
                        <option value="micronauta"> Micronauta</option>
                        <option value="megabus"> Megabus</option>
                    </select>
                </div>
                <?php } ?>
                <div class="col-md-6 position-relative" >
                    <label for="formato" class="form-label">Formato:</label>
                    <select class="form-select" id="formato" required="">
                        <option selected="" disabled="" value=""> Seleccione el formato</option>
                        <option value="html"> Mostrar en pantalla</option>
                        <option value="xls"> Excel (.xls)</option>
                        <option value="csv"> Separado por comas (.csv)</option>
                    </select>
                </div>
                <div class="buttonProgress" onclick="validarFormulario('formularioReporte', generarReporte)">
                    <div class="content">
                        <i class="bx bx-cloud-download"></i>
                        <span class="button-text" >Generar</span>
                    </div>
                </div>
            </form>
    </div>

<div class="container hidden animate__animated " id="contenidoReporte">
    <div class="divHeader container">
        <div class="header-section">
            <h2 class="title">Reporte de Usuarios y <span>Permisos</span></h2>
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

      	let servicio = 0;
        var element =  document.getElementById('servicio');
    	if (typeof(element) != 'undefined' && element != null)
    	{
	        servicio = document.getElementById("servicio");
    	    servicio = servicio.options[servicio.selectedIndex].value;
        }
        let formato = document.getElementById("formato");
        formato = formato.options[formato.selectedIndex].value;

        fetch("rep_listado_permisos_cmd.php",{
            method:'POST',
            headers:{
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cmd=generarReporte&form_listPermForm[formato_ver]=${formato}&form_listPermForm[origen]=${servicio}`
        })
        .then( peticion => peticion.json() )
        .then( res=>{
            if (res.redirectPage) {
                redirect(res.redirectPage);
                 return
            }
            if (res.ok) {
                let datosTabla = [["lastname","firstname"],"username","actionname"]
                let objPermisos = res.data;

                llenarDatosTabla("bodyTable", objPermisos, datosTabla);
                
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
                            contenidoReporte.classList.add("animate__fadeIn");
                        },500)

                    }, 4500)
                }
                
            }
            else{
                // Mensaje de error de la base de datos
            }

             
        })
        .catch(e => {
            toastNotification({ok: false, errorMsg: "Ha ocurrido un error de conexión."});
        })
    }

    function ocultarTabla(){
        let contenido = document.getElementById("contenido")
        let contenidoReporte = document.getElementById("contenidoReporte")

        contenidoReporte.classList.replace("animate__fadeIn", "animate__fadeOut");
        
        setTimeout(()=>{
            contenidoReporte.classList.add("hidden");
            contenido.classList.remove("animate__fadeOut");
            contenido.classList.remove("hidden");
            contenido.classList.add("animate__fadeInDown");
        },500)
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

