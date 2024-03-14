<?php require('estructura/header.php') ?>
<link rel="stylesheet noopener" href="../../modulos/toastNotification/toastNotification.css">
<link rel="stylesheet noopener" href="../../modulos/popup/popup.css">
<link rel="stylesheet noopener" href="../../modulos/searchBar/searchBar.css">
<link rel="stylesheet noopener" href="../../modulos/floatingButton/floatingButton.css">
<link rel="stylesheet noopener" href="../../modulos/floatingButton/floatingButtonActions.css">


<title>Grupos</title>
</head>
<style>
    .divHeader {
        display: flex;
    }


    td.uppercase {
        width: 30%;
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
                <h2 class="title">Lista de <span>Grupos</span></h2>
            </div>
            <div class="input-searchBar hidden animate__animated" id="searchBar">
                <input type="text" placeholder="Buscar..." id="search" onkeyup="cargarTabla()" autocomplete="nope">
                <span class="search">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                </span>
                <span class="closeSearchBar" onclick="closeSearchBar()">
                    <i class="fa-solid fa-xmark close-icon" id="close-icon"></i>
                </span>
            </div>
            <div class="circCont">
                <button class="circle boxShadow" data-animation="fadeOut" data-remove="3000"
                    onclick="redirect()"></button>
            </div>
        </div>


        <div class="table-responsive">
            <table class="table table-hover table-striped">
                <thead class="table-dark">
                    <tr>
                        <th scope="col"></th>
                        <!-- <th scope="col"></th>
                        <th scope="col"></th>
                        <th scope="col"></th> -->
                        <th class="centerText" scope="col" class="numberHeight">#</th>
                        <th class="centerText" scope="col">Nombre</th>
                        <th class="centerText" scope="col">Jerarquia</th>
                    </tr>
                </thead>
                <tbody id="bodyTable">
                </tbody>
            </table>
        </div>

        
    </div>
            
    <div class="overlay" id="overlay">
        <div class="popup" id="popup">
            <a href="#" id="btn-cerrar-popup" class="btn-cerrar-popup" onclick="closePopup()"><i class="fas fa-times"></i></a>
            <h4 id="tituloPopUp"></h4>
            <form id="formEditGroup">
                <div class="contenedor-inputs">
                    <input type="text" class="form-control me-2" placeholder="Nombre" id="inputEditarGrupo" autocomplete="nope">
                </div>
                <div class="contenedor-buttons">
                    <button class="btn btn-sm btn-primary" id="btnEditar">Aceptar</button>
                    <button class="btn btn-sm btn-danger" onclick="closePopup()">Cancelar</button>
                </div>
            </form>
            <form id="formDeleteGroup">
            <div class="contenedor-buttons">
                <button class="btn btn-sm btn-primary contenedor-buttons" id="btnEliminar">Aceptar</button>
                <button class="btn btn-sm btn-danger contenedor-buttons" onclick="closePopup()">Cancelar</button>
            </div>
            </form>
            <form id="formNewGroup">
                <div class="contenedor-inputs">
                    <input type="text" class="form-control me-2" placeholder="Añadir Nuevo Grupo" id="groupname" autocomplete="nope">
                </div>
                <div class="contenedor-buttons">
                    <button class="btn btn-sm btn-primary contenedor-buttons" id="btnAñadir">Añadir</button>
                    <button class="btn btn-sm btn-danger contenedor-buttons" onclick="closePopup()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="actionFB animate__animated animate__faster" id="floatingButton" onclick="actionToggle()">
        <span class="iconToggle">
            <i class="fa-solid fa-ellipsis"></i>
        </span>
        <ul>
            <li onclick="openPopupAñadir()"><i class="fa-solid fa-plus icono"></i> Nuevo Grupo</li>
            <li onclick="openSearchBar()"><i class="fa-solid fa-magnifying-glass icono"></i>Buscar</li>
        </ul>
    </div>


<?php require('estructura/footer.php') ?>
<script src="../../modulos/toastNotification/toastNotification.js" ></script>
<script src="../../modulos/searchBar/searchBar.js" ></script>
<script src="../../modulos/floatingButton/floatingButton.js" ></script>


<script>

	var overlay = document.getElementById('overlay'),
	popup = document.getElementById('popup'),
	btnCerrarPopup = document.getElementById('btn-cerrar-popup'),

	btnCancelar = document.getElementById('btnCancelar'),
	btnEditar = document.getElementById('btnEditar'),
	btnAñadir = document.getElementById('btnAñadir'),
	btnEliminar = document.getElementById('btnEliminar'),

	tituloPopUp = document.getElementById('tituloPopUp'),

	formEditGroup = document.getElementById('formEditGroup'),
	formDeleteGroup = document.getElementById('formDeleteGroup'),
	formNewGroup = document.getElementById('formNewGroup'),

	inputEditarGrupo = document.getElementById('inputEditarGrupo'),
	inputNuevoGrupo = document.getElementById('groupname');

    var groupid;
    var hierarchy;

    function closePopup(){
        overlay.classList.remove('active');
        popup.classList.remove('active');
        inputEditarGrupo.innerHTML = "";
    }

    function openPopupEditar(groupname, groupIdSeleccionado, jerarquia){
        groupid = groupIdSeleccionado;
        hierarchy = jerarquia;
        tituloPopUp.innerHTML = "Ingrese el Nuevo Nombre del Grupo";
        formEditGroup.removeAttribute("style");
        formNewGroup.setAttribute("style", "display: none");
        formDeleteGroup.setAttribute("style", "display: none");
        overlay.classList.add('active');
        popup.classList.add('active');
        inputEditarGrupo.value = groupname;
    }

    function openPopupEliminar(groupname, groupIdSeleccionado, jerarquia){
        groupid = groupIdSeleccionado;
        hierarchy = jerarquia;
        tituloPopUp.innerHTML = "¿Seguro que desea eliminar el grupo "+ groupname +" ?";
        formDeleteGroup.removeAttribute("style");
        formNewGroup.setAttribute("style", "display: none");
        formEditGroup.setAttribute("style", "display: none");
        overlay.classList.add('active'); 
        popup.classList.add('active');
    }

    function openPopupAñadir(){
        tituloPopUp.innerHTML = "Ingrese el Nombre del Nuevo Grupo";
        formNewGroup.removeAttribute("style");
        formEditGroup.setAttribute("style", "display: none");
        formDeleteGroup.setAttribute("style", "display: none");
        overlay.classList.add('active');
        popup.classList.add('active');
        inputNuevoGrupo.value = "";
    }
    
    btnEditar.addEventListener('click', ()=>{
        editarGrupo(groupid,hierarchy);
    });

    btnEliminar.addEventListener('click', ()=>{
        eliminarGrupo(groupid,hierarchy);
    });

    btnAñadir.addEventListener('click', ()=>{
        nuevoGrupo();
    });
</script>

<script>
    if(localStorage.getItem('dark-mode') === 'true'){
        document.querySelector('.table-striped').classList.remove('table-striped');
        document.querySelector('.table-dark').classList.remove('table-dark')
    }

    let formularios = document.querySelectorAll("#formNewGroup, #formEditGroup, #formDeleteGroup");

    formularios.forEach(formulario => {
        formulario.addEventListener("submit", (e) => {
            e.preventDefault();
        })
    });
    
    function cargarTabla(){
        let search = document.getElementById("search").value;
        
        fetch("groups_cmd.php",{
            method:'POST',
            headers:{
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cmd=cargarTabla&search=${search}`

        })
        .then( peticion => peticion.json() )
        .then( res=>{
            if (res.redirectPage) {
                redirect(res.redirectPage);
                 return
            }
            if (res.ok) {
                let objGroups = res.data;
                let bodyTable = document.getElementById("bodyTable");
                bodyTable.innerHTML = "";

                var docFragment = document.createDocumentFragment();
                
                for (let i = 0; i < objGroups.length; i++) {
                    
                    let tr = document.createElement("tr");
                    
                    let groupname = objGroups[i]['groupname'];
                    let groupid = objGroups[i]['groupid'];
                    let groupHierarchy = objGroups[i]['hierarchy'];
                    
                    let btnListar = `redirect('groupList.php?groupId=${objGroups[i]["groupid"]}&groupNombre=${objGroups[i]["groupname"]}')`
                    let btnEditar = `openPopupEditar('${groupname}','${groupid}', '${groupHierarchy}')`
                    let btnEliminar = `openPopupEliminar('${groupname}','${groupid}', '${groupHierarchy}')`;
                    let botonAction = 
                    `<td class="actionFBA" id="actionfba${i}" onclick="actionToggleActionMenu('actionfba${i}')" >
                        <div class="iconoMenu" id="first"><i class="fa-solid fa-plus"></i></div>
                                    <ul>
                                    <li onclick="${btnListar}"><i class="fa-solid fa-user icono list"></i>Listar Usuarios</li>`;
                    botonAction+=  (objGroups[i]["edit"] ? `<li onclick="${btnEditar}"><i class="fa-solid fa-pencil icono edit"></i>Editar</li>`: `` );
                    botonAction+=  (objGroups[i]["delete"] ? `<li onclick="${btnEliminar}"><i class="fa-solid fa-trash icono delete"></i>Eliminar</li>`: `` );
                    botonAction+= `</ul></td>`;
                    // <div >
                    //     <div class="iconoDiv" id="first"><i class="fa-solid fa-minus"></i></div>
                    //     <div class="iconoDiv" id="second"><i class="fa-solid fa-minus"></i></div>
                    //     </div>
            

// let divIconoOpen = document.createElement("div");
//   divIconoOpen.classList.add("icono")
//   divIconoOpen.setAttribute("id", "first");
//   let iconoOpen = document.createElement("i");
//   iconoOpen.classList.add("fa-solid");
//   iconoOpen.classList.add("fa-minus");

//   let divIconoOpen2 = document.createElement("div");
//   divIconoOpen2.classList.add("icono")
//   divIconoOpen2.setAttribute("id", "second");
//   let iconoOpen2 = document.createElement("i");
//   iconoOpen2.classList.add("fa-solid");
//   iconoOpen2.classList.add("fa-minus");

//   divIconoOpen.appendChild(iconoOpen)
//   divIconoOpen2.appendChild(iconoOpen2)
                    
                    
                    tr.innerHTML += botonAction;

                    // let botonListar = `<td class="centerText buttonHeight"><button class="btn btn-sm btn-primary" id="btnListar" title="Usuarios" onClick="redirect('groupList.php?groupId=${objGroups[i]["groupid"]}&groupNombre=${objGroups[i]["groupname"]}')"><i class="fa-solid fa-user"></i></button></td>`
                    // tr.innerHTML += botonListar;

                    // let a = ["btn-info","btn-danger"];
                    // let b = ["btn-outline-info","btn-outline-danger"];
                    // let c = ["Editar", "Eliminar"];
                    // let d = ["edit","delete"];
                    // let e = ["fa-pencil","fa-trash"];
                    
                    // for (let j = 0; j < 2; j++) {
                    //     let tdBtn = document.createElement("td");
                    //     tdBtn.classList.add("buttonHeight");

                    //     if (objGroups[i][d[j]]) {
                    //         tdBtn.classList.add("centerText");
                    //         let btn = document.createElement("button");
                    //         btn.classList.add("btn");
                    //         btn.classList.add("btn-sm");
                    //         btn.classList.add(a[j]);
                    //         btn.setAttribute("title",c[j]);
                    //         if (j == 0) {
                    //             btn.setAttribute("onClick",`openPopupEditar("${objGroups[i]['groupname']}","${objGroups[i]['groupid']}", "${objGroups[i]['hierarchy']}")` );
                    //         }
                    //         else{
                    //             btn.setAttribute("onClick",`openPopupEliminar("${objGroups[i]['groupname']}","${objGroups[i]['groupid']}", "${objGroups[i]['hierarchy']}")` );
                    //         }
                            
                    //         let icono = document.createElement("i");
                    //         icono.classList.add("fa-solid");
                    //         icono.classList.add(e[j]);

                    //         btn.appendChild(icono);
                    //         tdBtn.appendChild(btn);

                    //     }
                    //     tr.appendChild(tdBtn);
                    // }
                    
                    let thOrden = document.createElement("th");
                    thOrden.innerHTML= i+1;
                    thOrden.classList.add("numberHeight")
                    thOrden.classList.add("centerText");
                    
                    let tdNombreGrupo = document.createElement("td");
                    tdNombreGrupo.innerHTML= objGroups[i]["groupname"];
                    tdNombreGrupo.classList.add("uppercase");
                    tdNombreGrupo.classList.add("centerText");



                    let tdJerarquia = document.createElement("td");
                    tdJerarquia.innerHTML= objGroups[i]["hierarchy"];
                    tdJerarquia.classList.add("centerText");

                    tr.appendChild(thOrden);
                    tr.appendChild(tdNombreGrupo);
                    tr.appendChild(tdJerarquia);
                    
                    
                    docFragment.appendChild(tr)
                    
                }
                bodyTable.appendChild(docFragment);

                
            }
            else{
                // Mensaje de error de la base de datos
            }

             
        })
        .catch(e => {
            toastNotification({ok: false, errorMsg: "Ha ocurrido un error de conexión."});
        })
    }
    
    function nuevoGrupo(){
        let groupname = document.getElementById("groupname");
        
        fetch("groups_cmd.php",{
            method:'POST',
            headers:{
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: `cmd=nuevoGrupo&form_GroupsForm[groupname]=${groupname.value}`
            
        })
        .then( peticion => peticion.json() )
        .then( res=>{
            toastNotification(res);
            limpiarFormulario();
            closePopup();
            cargarTabla();
        })

    }  

    function editarGrupo(groupId, hierarchy){

        let groupname = document.getElementById("inputEditarGrupo").value;
        
        fetch("groups_cmd.php",{
            method:'POST',
            headers:{
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cmd=editarGrupo&form_GroupsForm[groupname]=${groupname}&form_GroupsForm[groupId]=${groupId}&form_GroupsForm[hierarchy]=${hierarchy}`

        })
        .then( peticion => peticion.json() )
        .then( res=>{
             if (res.redirectPage) {
                redirect(res.redirectPage);
                 return
            }
            toastNotification(res);
            limpiarFormulario();
            closePopup();
            cargarTabla();

        })
        .catch(e => {
            toastNotification({ok: false, errorMsg: "Ha ocurrido un error de conexión."});
        })
    }

    function eliminarGrupo(groupId, hierarchy){

        fetch("groups_cmd.php",{
            method:'POST',
            headers:{
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `cmd=eliminarGrupo&form_GroupsForm[groupId]=${groupId}&form_GroupsForm[hierarchy]=${hierarchy}`

        })
        .then( peticion => peticion.json() )
        .then( res=>{
             if (res.redirectPage) {
                redirect(res.redirectPage);
                 return
            }
            toastNotification(res);
            limpiarFormulario();
            closePopup();
            cargarTabla();

        })
        .catch(e => {
            toastNotification({ok: false, errorMsg: "Ha ocurrido un error de conexión."});
        })
    }
    

window.onload = ()=>{
    cargarTabla();
}

function redirect(pagina = 'usuarioServices.html') {
        let contenido = document.getElementById("contenido");
        contenido.classList.remove('animate__fadeInDown');
        contenido.classList.add('animate__fadeOutUp');

               setTimeout(() => {
             setTimeout(() => {
                contenido.classList.replace('animate__fadeOutUp','animate__fadeInDown');
            }, 500);
            window.location = pagina;
        }, 600);
    }

</script>
</body>
</html>