 /*const msg = document.getElementById('message');
 const form = document.getElementById('contactForm');
 const fName=document.getElementById("fname");
 const lName=document.getElementById("lname");
 const email=document.getElementById("email");
 const errorMessages = document.getElementById("errors");

form.addEventListener('submit', (e) => {
    let errors=[]
    errorMessages.innerHTML='';
    errorMessages.style.display='none';
    if (fName.value==='' || fName.value==null){
        errors.push('First name is required');
    }
    /*let fName = form.elements['first_name'].value
    .trim()                    // remove spaces
    .replace(/[\u200B-\u200D\uFEFF]/g, ''); // remove invisible characters

    let lName = form.elements['last_name'].value
    .trim()
    .replace(/[\u200B-\u200D\uFEFF]/g, '');*/
        // [^...]: This is a negated character set.
        /*
    if (/[^a-zA-ZÀ-ÖØ-öø-ÿ\s-]/.test(fName) || /[^a-zA-ZÀ-ÖØ-öø-ÿ\s-]/.test(lName)) {  
        errors.push("Name must contain only letters, hyphens or spaces.");
    }   
    if (lName.value==='' || lName.value==null){
        errors.push('Last name is required');
    }
    if (email.value==='' || email.value==null){
        errors.push('email is required');
    }
    if (msg.value==='' || msg.value==null){
        errors.push('Your message is required');
    }

    if(errors.length>0){
        e.preventDefault();
        errorMessages.innerHTML = errors.join('<br>');
        errorMessages.style.display = 'block';
    }

    // Grab the form element
    let form = e.target;

    // Ensure all whitespace is trimmed (optional but good)
    Array.from(form.elements).forEach(el => {
        if (el.type === 'text' || el.type === 'email' || el.tagName === 'TEXTAREA') {
            el.value = el.value.trim();
        }
    });

    // Create FormData
    let formData = new FormData(form);

    // --- 1. Send to PHP (database) ---
    fetch('http://localhost/innerbloom/assets/php/about.php', {  // <-- correct relative path from your HTML file
        method: 'POST',
        body: formData
    })
    .then(res => res.text())
    .then(data => {
        console.log('PHP response:', data);

        // Make sure this element exists in your HTML:
        // <div id="response"></div>
        let responseDiv = document.getElementById('response');
        if (responseDiv) {
            responseDiv.innerHTML = data;
        }
    })
    .catch(err => console.error('PHP error:', err));

    // --- 2. Send to Web3Forms API ---
    fetch('https://api.web3forms.com/submit', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => console.log('Web3Forms response:', data))
    .catch(err => console.error('Web3Forms error:', err));
});
 function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}*/

const msg = document.getElementById('message');
const form = document.getElementById('contactForm');
const fNameInput = document.getElementById("fname");
const lNameInput = document.getElementById("lname");
const emailInput = document.getElementById("email");
const errorMessages = document.getElementById("errors");

// Validation functions
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function isValidName(name) {
    // Allow letters, spaces, hyphens, and common accented characters
    return /^[a-zA-ZÀ-ÖØ-öø-ÿ\s-]+$/.test(name);
}

form.addEventListener('submit', function(e) {
    e.preventDefault(); // prevent default to handle everything in JS
    
    let errors = [];
    errorMessages.innerHTML = '';
    errorMessages.style.display = 'none';
    
    // Get and trim values
    let firstName = fNameInput.value.trim();
    let lastName = lNameInput.value.trim();
    let email = emailInput.value.trim();
    let message = msg.value.trim();
    
    // Validate
    if (!firstName) {
        errors.push('First name is required');
    } else if (!isValidName(firstName)) {
        errors.push("First name must contain only letters, hyphens or spaces.");
    }
    
    if (!lastName) {
        errors.push('Last name is required');
    } else if (!isValidName(lastName)) {
        errors.push("Last name must contain only letters, hyphens or spaces.");
    }
    
    if (!email) {
        errors.push('Email is required');
    } else if (!isValidEmail(email)) {
        errors.push('Please enter a valid email address');
    }
    
    if (!message) {
        errors.push('Your message is required');
    }
    
    // If there are errors, show them
    if (errors.length > 0) {
        errorMessages.innerHTML = errors.join('<br>');
        errorMessages.style.display = 'block';
        return; // Stop execution
    }
   
    // Prepare form data
    let formData = new FormData(form);
    
    // Send to PHP
    fetch('http://localhost/innerbloom/assets/php/about.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(data => {
        console.log('PHP response:', data);
        
        // Display response
        let responseDiv = document.getElementById('response');
        if (responseDiv) {
            responseDiv.innerHTML = data;
        }

        let web3FormData = new FormData(form);
        form.reset();
        // Also send to Web3Forms 
        return fetch('https://api.web3forms.com/submit', {
            method: 'POST',
            body: web3FormData
        });
    })
    .then(response => response.text())
    .then(web3Data => {
        console.log('Web3Forms response:', web3Data);
    })
    .catch(error => {
        console.error('Error:', error);
        let responseDiv = document.getElementById('response');
        if (responseDiv) {
            responseDiv.innerHTML = `<div style="color: red;">Error: ${error.message}</div>`;
        }
    });
});
