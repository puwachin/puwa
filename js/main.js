

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

function openSeries(imageList, title = "") {
  const viewer = document.getElementById("lightbox-viewer");
  const overlay = viewer.querySelector(".lightbox-overlay");
  const content = viewer.querySelector(".lightbox-content");

  // クリア
  content.innerHTML = "";

  // タイトル（あれば）
  if (title) {
    const titleElem = document.createElement("div");
    titleElem.className = "lightbox-title";
    titleElem.textContent = title;
    content.appendChild(titleElem);
  }

  // 画像追加
  imageList.forEach(src => {
    const img = document.createElement("img");
    img.src = src;
    img.alt = "comic image";
    content.appendChild(img);
  });

  // 閉じるボタン

  viewer.classList.add("open");
}

// ページ読み込み後にクリックイベントを設定
window.addEventListener("DOMContentLoaded", () => {
  const triggers = document.querySelectorAll("[data-images]");
  triggers.forEach(trigger => {
    trigger.addEventListener("click", () => {
      const images = JSON.parse(trigger.getAttribute("data-images"));
      const title = trigger.getAttribute("data-title") || "";
      openSeries(images, title);
    });
  });
});

document.addEventListener("DOMContentLoaded", () => {
  const viewer = document.getElementById("lightbox-viewer");
  const overlay = document.querySelector(".lightbox-overlay");

  // 背景（オーバーレイ）をタップしたら閉じる（スマホ対応）
  overlay.addEventListener("click", () => {
    viewer.classList.remove("open");
  });
});
