// WEBSITE LOADED
console.log("Modern Portfolio Website Loaded");

// CONTACT FORM
const form = document.getElementById("contactForm");

if(form){

    form.addEventListener("submit", function(e){

        e.preventDefault();

        alert("Message Sent Successfully!");

        form.reset();

    });

}

// BUTTON ANIMATION
const buttons = document.querySelectorAll("button");

buttons.forEach((button)=>{

    button.addEventListener("mouseover", ()=>{

        button.style.transform = "scale(1.05)";

    });

    button.addEventListener("mouseout", ()=>{

        button.style.transform = "scale(1)";

    });

});