<?php require('estructura/header.php') ?>
<link rel="stylesheet noopener" href="../../modulos/popup/popup.css">
<link rel="stylesheet noopener" href="../../modulos/toastNotification/toastNotification.css">
<script src="https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js"></script>

<title>Grupos</title>
</head>
<style>
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
                <h2 class="title" >Lista de Usuarios de
                    <span id="nombreGrupo"></span>
                </h2>
            </div>
            <div class="circCont">
                <button class="circle boxShadow" data-animation="fadeOut" data-remove="3000"
                    onclick="redirect('groups.php')"></button>
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover ">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Usuario</th>
                        <th scope="col">Editar</th>
                        <th scope="col">Eliminar</th>
                    </tr>
                </thead>
            <tbody id="bodyTable">
            </tbody>
            </table>
        </div>
        <div class="hidden" id="msgDataFound">
            <div class="divLottie">
                <lottie-player class="" id="userActivo" src="./images/search-not-found.json" background="transparent"  speed="1"  style="width: 200px; height: 200px;" autoplay loop></lottie-player>            
            </div>
        </div>
    </div>

    <div class="overlay" id="overlay">
        <div class="popup" id="popup">
            <a href="#" id="btn-cerrar-popup" class="btn-cerrar-popup" onclick="closePopup()"><i class="fas fa-times"></i></a>
            <h4 id="tituloPopUp"></h4>
            <form id="formDeleteAccount">
            <div class="contenedor-buttons">
                <button class="btn btn-sm btn-primary contenedor-buttons" id="btnEliminar">Aceptar</button>
                <button class="btn btn-sm btn-danger contenedor-buttons" onclick="closePopup()">Cancelar</button>
            </div>
            </form>
        </div>
    </div>

    

    <script src="../../modulos/toastNotification/toastNotification.js" ></script>
    <?php require('estructura/footer.php') ?>
<script>




var overlay = document.getElementById('overlay'),
popup = document.getElementById('popup'),
btnCerrarPopup = document.getElementById('btn-cerrar-popup'),
btnEliminar = document.getElementById('btnEliminar'),
tituloPopUp = document.getElementById('tituloPopUp'),
formDeleteAccount = document.getElementById('formDeleteAccount');

formDeleteAccount.addEventListener("submit", (e) => {
    e.preventDefault();
})

var accountid;

function closePopup(){
    overlay.classList.remove('active');
    popup.classList.remove('active');
}

function openPopupEliminar(accountId, username){
    accountid = accountId;
    tituloPopUp.innerHTML = "¿Seguro que desea eliminar al usuario "+ username +" del grupo ?";
    overlay.classList.add('active'); 
    popup.classList.add('active');
}

btnEliminar.addEventListener('click', ()=>{
    eliminarUsuarioDelGrupo(accountid);
});


</script>


<script>


    function cargarTitulo(){
        let spanNombreGrupo = document.getElementById("nombreGrupo");
        let nombreGrupo = getParameterByName('groupNombre');

        spanNombreGrupo.innerHTML= nombreGrupo;
    }

    function cargarTabla(){

        fetch("groupList_cmd.php",{
        method:'POST',
        headers:{
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: `cmd=listarGrupo&groupId=<?php echo $_GET['groupId'];?>`
        })
        .then( peticion => peticion.json() )
        .then( res=>{
            if (res.redirectPage) {
                redirect(res.redirectPage);
                return
            }
            let objListGroups = res.data;
            let bodyTable = document.getElementById("bodyTable");
            bodyTable.innerHTML = "";

            var docFragment = document.createDocumentFragment();

            if(objListGroups.length != 0){
                for (let i = 0; i < objListGroups.length; i++) {
        
        
                    let tr = document.createElement("tr");
                    tr.classList.add("animate__animated");
                    tr.classList.add("animate__fadeIn");
                    tr.classList.add("uppercase");
        
                    
                    let thOrden = document.createElement("th");
                    thOrden.innerHTML= i+1;
        
                    let tdNombre = document.createElement("td");
                    tdNombre.innerHTML= objListGroups[i]["lastname"] + ", " + objListGroups[i]["firstname"];
        
                    let tdUsuario = document.createElement("td");
                    tdUsuario.innerHTML= objListGroups[i]["username"];
        
                    tr.appendChild(thOrden);
                    tr.appendChild(tdNombre);
                    tr.appendChild(tdUsuario);
        
                    let a = ["btn-info","btn-danger"];
                    let b = ["btn-outline-info","btn-outline-danger"];
                    let c = ["Editar", "Eliminar"];
                    let d = ["edit","delete"];
                    let e = ["fa-pencil","fa-trash"];
        
                    for (let j = 0; j < 2; j++) {
                        let tdBtn = document.createElement("td");

                        if (objListGroups[i][d[j]]) {

                            let btn = document.createElement("button");
                            btn.classList.add("btn");
                            btn.classList.add("btn-sm");
                            btn.classList.add(a[j]);
                            btn.setAttribute("title",c[j]);
                            if (j == 0) {
                                btn.setAttribute("onClick",`redirect('accountsModify.php?accountId=${objListGroups[i]["accountid"]}')` );
                            }
                            else{
                                btn.setAttribute("onClick",`openPopupEliminar("${objListGroups[i]['accountid']}","${objListGroups[i]['username']}")` );
                            }
                            
                            let icono = document.createElement("i");
                            icono.classList.add("fa-solid");
                            icono.classList.add(e[j]);
            
                            btn.appendChild(icono);
                            tdBtn.appendChild(btn);
                        }

        
                        tr.appendChild(tdBtn);
                    }
        
                    docFragment.appendChild(tr)
                    
                }
                bodyTable.appendChild(docFragment);
            }
            else{
                let msgDataFound = document.getElementById("msgDataFound");
                msgDataFound.classList.remove("hidden");
            }


        })
        
    }

    function eliminarUsuarioDelGrupo(accountid){
        let formDeleteAccount = document.getElementById("formDeleteAccount");

        formDeleteAccount.addEventListener("submit", (e) => {
            e.preventDefault();
        })

        fetch("accountsModify_cmd.php",{
            method:'POST',
            headers:{
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body:  `cmd=quitarGrupos&accountId=${accountid}&form_AccountsForm[accountgroups]=` + getParameterByName('groupId')
        })
        .then( peticion => peticion.json() )
        .then( res=>{
            if (res.redirectPage) {
                redirect(res.redirectPage);
                return
            }
            toastNotification(res);
            closePopup();
            cargarTabla();
             
        })
        .catch(e => {
            // toastNotification({ok: false, errorMsg: "Ha ocurrido un error de conexión."});
        })

    }

window.onload = ()=>{
    cargarTitulo();
    cargarTabla();
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