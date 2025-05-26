
function openSeries(images) {
  const container = document.getElementById("lightbox-content");
  container.innerHTML = '';
  images.forEach(src => {
    const img = document.createElement("img");
    img.src = src;
    container.appendChild(img);
  });
  document.getElementById("lightbox").style.display = "flex";
}

function closeLightbox() {
  document.getElementById("lightbox").style.display = "none";
}

function openSeries(images) {
  const container = document.getElementById("lightbox-content");
  container.innerHTML = '';

  images.forEach(src => {
    const img = document.createElement("img");
    img.src = src;
    container.appendChild(img);
  });

  const spacer = document.createElement("div");
  spacer.style.height = "100vh";
  container.appendChild(spacer);

  document.getElementById("lightbox").style.display = "flex";
}