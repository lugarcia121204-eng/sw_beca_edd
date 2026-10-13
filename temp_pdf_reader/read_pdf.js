const fs = require('fs');
const pdf = require('pdf-parse');

let d1 = fs.readFileSync('c:/laragon/www/BecasLaravel_AD/DocumentosSobreConvocatoria/CONVOCATORIA_EDD_2025.pdf');
pdf(d1).then(data => {
    console.log('--- CONVOCATORIA_EDD_2025 ---');
    console.log(data.text.substring(0, 1000));
});

let d2 = fs.readFileSync('c:/laragon/www/BecasLaravel_AD/DocumentosSobreConvocatoria/FORMATO_SPD_2025.pdf');
pdf(d2).then(data => {
    console.log('\n--- FORMATO_SPD_2025 ---');
    console.log(data.text.substring(0, 1000));
});
