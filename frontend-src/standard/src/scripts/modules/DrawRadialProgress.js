export function drawRadialProgress(canvas) {
    //console.log(canvas);
    const percentage = canvas.getAttribute('data-percentage_completed');
    //console.log("percentage"+percentage);
    const context = canvas.getContext('2d');
    const centerX = canvas.width / 2;
    const centerY = canvas.height / 2;
    const radius = 60;
    const startAngle = 1.5 * Math.PI;
    const endAngle = startAngle + (percentage / 100) * (2 * Math.PI);

    context.clearRect(0, 0, canvas.width, canvas.height);

    context.beginPath();
    context.arc(centerX, centerY, radius, 0, 2 * Math.PI);
    context.lineWidth = 30;
    context.strokeStyle = getComputedStyle(canvas).getPropertyValue('--background-color');
    context.stroke();

    context.beginPath();
    context.arc(centerX, centerY, radius, startAngle, endAngle);
    context.lineWidth = 20;
    context.strokeStyle = getComputedStyle(canvas).getPropertyValue('--foreground-color');
    context.stroke();

    context.font = '22px Open-sans';
    context.fillStyle = getComputedStyle(canvas).getPropertyValue('--foreground-color');
    context.textAlign = 'center';
    context.textBaseline = 'middle';
    context.fillText(percentage + '%', centerX, centerY);
}

