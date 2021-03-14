/** @format */

const cancelFormButton = document.querySelector("#cancel-form");
const registerFormButton = document.querySelector("#register-form");
const displayFormButton = document.querySelector("#display-form");
const cloak = document.querySelector("#cloak");
const form = document.querySelector(".form");

displayFormButton.addEventListener("click", () => {
	cloak.style.visibility = form.style.visibility = "visible";
});

cancelFormButton.addEventListener("click", () => {
	cloak.style.visibility = form.style.visibility = "hidden";
});

registerFormButton.addEventListener("click", (evt) => {});
