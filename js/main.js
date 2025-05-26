

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

document.querySelectorAll('.chara').forEach(character => {
  // 初期表示で確実にフェードインするように
  character.classList.add('animated');

  character.addEventListener('animationend', (e) => {
    if (e.animationName === 'fadeInUp') {
      character.classList.add('animated');
    }
  });

  character.addEventListener('click', () => {
    character.classList.remove('jump');
    void character.offsetWidth;
    character.classList.add('jump');
  });
});


const style = document.createElement('style');
style.innerHTML = `
@keyframes jump {
  0% { transform: translateY(0); }
  30% { transform: translateY(-20px); }
  50% { transform: translateY(0); }
  100% { transform: translateY(0); }
}
.chara.jump {
  animation: jump 0.4s ease;
}`;
document.head.appendChild(style);