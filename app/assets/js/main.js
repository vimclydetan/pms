let profile = document.querySelector('.header .flex .drop');

document.querySelector('#user-btn').onclick = () =>{
   profile.classList.toggle('active');
   searchForm.classList.remove('active');
}

let sidebar = document.querySelector(".side-bar");

document.addEventListener("DOMContentLoaded", function() {
    document.getElementById("menu-btn").addEventListener("click", function () {
        sidebar.classList.toggle("collapsed");
    });
});

