// form validation for recipes
function validateRecipeForm(formId) {
    // fetch form using ID
    const form = document.getElementById(formId);
    if (!form) return; // if no form found, exit

    // add event listener to the form the submit event
    form.addEventListener('submit', function (e) {
        // define which fileds are required
        const requiredFields = ['recipe_name', 'preptime', 'cooktime', 'ingredients', 'instructions'];
        let isValid = true; // flag to check form validity

        // remove any existing error messages
        document.querySelectorAll('.error').forEach(error => error.remove());

        // check if each required field is filled
        requiredFields.forEach(field => {
            const input = document.getElementById(field);
            if (!input.value.trim()) { // if field empty or has whitespace
                isValid = false; // set flag to false (invalidate form)
                showError(input, 'This field is required'); // show error message
            }
        });

        // check if preptime and cooktime are positive integers
        ['preptime', 'cooktime'].forEach(field => {
            const input = document.getElementById(field);
            if (parseInt(input.value) < 0) { // check if value is negative
                isValid = false; // invalidate form
                showError(input, 'Please enter a positive number'); // show error message
            }
        });

        // prevent form submission if any validations fail
        if (!isValid) {
            e.preventDefault();
        }
    });
}

// display error message below form element
function showError(element, message) {
    // create new div element for error message
    const error = document.createElement('div');
    error.className = 'error'; // set class for styling
    error.textContent = message; // set error message content
    element.parentNode.appendChild(error); // insert error div after input element
}

// delete recipe confirmation message
function confirmDelete(recipeId) {
    // show confirmation dialog
    if (confirm('Are you sure you want to delete this recipe? This action cannot be undone.')) {
        // if confirmed, redirect to delete URL
        window.location.href = 'delete_recipe.php?id=' + recipeId;
    }
}

// add difficulty badge classes based on content
function initializeDifficultyBadge() {
    // find element with class difficulty-badge
    const difficultyBadge = document.querySelector('.difficulty-badge');
    if (difficultyBadge) {
        // get difficulty level from badge, convert to lowercase and trim whitespace
        const difficulty = difficultyBadge.textContent.toLowerCase().trim();
        // add difficulty level as class to badge for styling
        difficultyBadge.classList.add(difficulty);
    }
}

// set up preview image file selected before upload
function initializeImagePreview() {
    // get file input and preview container
    const imageInput = document.getElementById('image');
    const previewContainer = document.getElementById('imagePreview');
    if (imageInput && previewContainer) {
        // add event listener that triggers when file changes
        imageInput.addEventListener('change', function (e) {
            const file = e.target.files[0]; // get uploaded file
            if (file) {
                const reader = new FileReader(); // read the file
                reader.onload = function (e) {
                    // set innerHRML of the preview container
                    previewContainer.innerHTML = `
                        <img src="${e.target.result}" alt="Preview" style="max-width: 200px;">
                        <p>Preview of new image</p>
                    `;
                };
                reader.readAsDataURL(file);  // read file as URL
            }
        });
    }
}

// display success message
function handleSuccessMessage() {
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('message'); // get message from URL
    if (message) {
        const successMessage = document.getElementById('success-message');
        if (successMessage) {
            successMessage.textContent = decodeURIComponent(message); // decode and display message
            successMessage.style.display = 'block'; // make message visible
            setTimeout(() => { // hide message after 3 seconds
                successMessage.style.display = 'none';
            }, 3000);
        }
    }
}

// initialize all form handlers
function initializeFormHandlers() {
    validateRecipeForm('recipeForm'); // initialize form validation
    initializeImagePreview(); // initialize image preview
}

// initialize all functionalities when DOM is loaded
document.addEventListener('DOMContentLoaded', function () {
    initializeFormHandlers(); // set form handlers
    initializeDifficultyBadge(); // set difficulty badges
    handleSuccessMessage(); // handle any success messages
});