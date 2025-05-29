

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
  spacer.style.height = "25vh";
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

// Lightbox機能
document.addEventListener("DOMContentLoaded", function () {
  const viewer = document.createElement("div");
  viewer.id = "lightbox-viewer";
  viewer.innerHTML = `<div class="lightbox-overlay"></div><div class="lightbox-content"><img><p class="lightbox-title"></p><button class="lightbox-close">×</button></div>`;
  document.body.appendChild(viewer);

  const overlay = viewer.querySelector(".lightbox-overlay");
  const img = viewer.querySelector("img");
  const title = viewer.querySelector(".lightbox-title");
  const close = viewer.querySelector(".lightbox-close");

  function openLightbox(src, caption) {
    img.src = src;
    title.textContent = caption || "";
    viewer.classList.add("open");
    document.body.style.overflow = "hidden";
  }

  function closeLightbox() {
    viewer.classList.remove("open");
    document.body.style.overflow = "";
  }

  overlay.addEventListener("click", closeLightbox);
  close.addEventListener("click", closeLightbox);

  document.querySelectorAll(".card img").forEach((image) => {
    image.addEventListener("click", function () {
      const src = this.src;
      const caption = this.dataset.title || this.alt;
      openLightbox(src, caption);
    });
  });
});

const image = document.createElement('img');
image.src = imgUrl;
image.className = 'thumbnail';

const card = document.createElement("div");
card.className = "card";
card.appendChild(img);
container.appendChild(card);