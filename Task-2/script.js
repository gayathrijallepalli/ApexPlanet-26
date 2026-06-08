// script.js

const loginBtn = document.getElementById("loginBtn");
const registerBtn = document.getElementById("registerBtn");

const loginForm = document.getElementById("loginForm");
const registerForm = document.getElementById("registerForm");


// SWITCH TO LOGIN FORM

loginBtn.addEventListener("click", () => {

    loginForm.classList.remove("d-none");
    registerForm.classList.add("d-none");

    loginBtn.classList.add("active-btn");
    registerBtn.classList.remove("active-btn");

});


// SWITCH TO REGISTER FORM

registerBtn.addEventListener("click", () => {

    registerForm.classList.remove("d-none");
    loginForm.classList.add("d-none");

    registerBtn.classList.add("active-btn");
    loginBtn.classList.remove("active-btn");

});


// SHOW/HIDE PASSWORD

function togglePassword(id){

    const input = document.getElementById(id);

    if(input.type === "password"){

        input.type = "text";

    }

    else{

        input.type = "password";

    }

}


// PASSWORD CHECK BEFORE SUBMIT

registerForm.addEventListener("submit", function(e){

    const password =
        document.getElementById("registerPassword").value;

    const confirmPassword =
        document.getElementById("confirmPassword").value;


    // STOP FORM IF PASSWORDS DON'T MATCH

    if(password !== confirmPassword){

        e.preventDefault();

        alert("Passwords do not match ❌");

    }

});