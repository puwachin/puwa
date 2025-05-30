


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


// ✅ ライトボックスを開く関数
function openSeries(images, title = "") {
  const viewer = document.getElementById("lightbox-viewer");
  const content = viewer.querySelector(".lightbox-content");

  content.innerHTML = "";

  if (title) {
    const titleElem = document.createElement("div");
    titleElem.className = "lightbox-title";
    titleElem.textContent = title;
    content.appendChild(titleElem);
  }

  images.forEach(src => {
    const img = document.createElement("img");
    img.src = src;
    img.alt = "comic image";
    content.appendChild(img);
  });

  viewer.classList.add("open");
}

document.addEventListener("DOMContentLoaded", () => {
  const viewer = document.getElementById("lightbox-viewer");
  const content = viewer.querySelector(".lightbox-content");

  // 画像をクリックしたらライトボックスを閉じる
  content.addEventListener("click", (e) => {
    if (e.target.tagName === "IMG") {
      viewer.classList.remove("open");
    }
  });
});

// ✅ カード生成＆クリックイベント設定
async function loadSection(folderId, sectionId) {
  try {
    const response = await fetch(`https://script.google.com/macros/s/AKfycbzsOUF9_3-R2HEvGXoLhyAKsA9cvGEbauwwYGR6kfmASwjULIX0N9S0JgX90a3LDTDSww/exec?id=${folderId}`);
    const data = await response.json();

    const section = document.getElementById(sectionId);
    section.innerHTML = "";

    data.folders.forEach(group => {
      const card = document.createElement("div");
      card.className = "comic-card";
      card.setAttribute("data-images", JSON.stringify(group.images));
      card.setAttribute("data-title", group.title || "");

      const caption = document.createElement("div");
      caption.className = "comic-caption";
      caption.textContent = group.title;
      card.appendChild(caption);

      const thumbnail = document.createElement("img");
      thumbnail.src = group.images[0];
      thumbnail.className = "thumbnail";
      card.appendChild(thumbnail);

      // ✅ クリックイベント
      card.addEventListener("click", () => {
        openSeries(group.images, group.title);
      });

      section.appendChild(card);
    });
  } catch (error) {
    console.error("読み込みエラー:", error);
  }
}

// ✅ 各セクションを読み込む部分（いじらない！）
document.addEventListener("DOMContentLoaded", () => {
  const rakugakiId = "1-1DLH8xvA1Mt6YZAGZluVpevqWmwjsJ0";
  const mangaId = "1-2_2G9hCjI65nr34Ys1KRoIcR47N7ln8";
  const etcId = "13k6aABhV2ooWFG6bQ_gnuwpFhCR6QHYO";

  loadSection(rakugakiId, "rakugaki-container");
  loadSection(mangaId, "manga-container");
  loadSection(etcId, "etc-container");
});