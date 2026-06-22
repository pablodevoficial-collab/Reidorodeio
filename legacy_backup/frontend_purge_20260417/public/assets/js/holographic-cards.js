// HOLOGRAPHIC CARDS INTERACTION SCRIPT

const holoInit = () => {
  const $cards = document.querySelectorAll(".card-holo");

  if (!$cards.length) return;

  $cards.forEach(($card) => {
    const $wrap = $card.closest(".card-wrapper-holo");
    if (!$wrap) return;

    const cardUpdate = (e) => {
      // normalise touch/mouse
      var pos = [e.offsetX, e.offsetY];
      if (e.pointerType === "touch") {
        e.preventDefault;
      }
      var dimensions = $card.getBoundingClientRect();
      // math for mouse position
      var l = pos[0];
      var t = pos[1];
      var h = dimensions.height;
      var w = dimensions.width;
      var px = clamp(Math.abs((100 / w) * l), 0, 100);
      var py = clamp(Math.abs((100 / h) * t), 0, 100);
      var cx = px - 50;
      var cy = py - 50;

      $wrap.setAttribute(
        "style",
        `
        --pointer-x: ${px}%;
        --pointer-y: ${py}%;
        --background-x: ${adjust(px, 0, 100, 35, 65)}%;
        --background-y: ${adjust(py, 0, 100, 35, 65)}%;
        --pointer-from-center: ${clamp(
          Math.sqrt((py - 50) * (py - 50) + (px - 50) * (px - 50)) / 50,
          0,
          1
        )};
        --pointer-from-top: ${py / 100};
        --pointer-from-left: ${px / 100};
        --rotate-x: ${round(-(cx / 5))}deg;
        --rotate-y: ${round(cy / 4)}deg;
      `
      );
    };

    function ease(x) {
      return x < 0.5 ? 4 * x * x * x : 1 - Math.pow(-2 * x + 2, 3) / 2;
    }

    function easedFunc(durationMs, onProgress, onComplete, onCancel) {
      let startTime = performance.now();
      let canceled = false;

      function loop() {
        if (canceled) return;
        const currentTime = performance.now();
        const progress = (currentTime - startTime) / durationMs;
        const easedProgress = ease(progress);
        onProgress(easedProgress);
        if (progress < 1) {
          requestAnimationFrame(loop);
        } else {
          if (onComplete) onComplete();
        }
      }

      loop();

      return {
        cancel: () => {
          canceled = true;
        },
      };
    }

    /**
     * return a value that has been rounded to a set precision
     * @param {Number} value the value to round
     * @param {Number} precision the precision (decimal places), default: 3
     * @returns {Number}
     */
    const round = (value, precision = 3) => parseFloat(value.toFixed(precision));

    /**
     * return a value that has been limited between min & max
     * @param {Number} value the value to clamp
     * @param {Number} min minimum value to allow, default: 0
     * @param {Number} max maximum value to allow, default: 100
     * @returns {Number}
     */
    const clamp = (value, min = 0, max = 100) => {
      return Math.min(Math.max(value, min), max);
    };

    /**
     * return a value that has been re-mapped according to the from/to
     * - for example, adjust(10, 0, 100, 100, 0) = 90
     * @param {Number} value the value to re-map (or adjust)
     * @param {Number} fromMin min value to re-map from
     * @param {Number} fromMax max value to re-map from
     * @param {Number} toMin min value to re-map to
     * @param {Number} toMax max value to re-map to
     * @returns {Number}
     */
    const adjust = (value, fromMin, fromMax, toMin, toMax) => {
      return round(
        toMin + ((toMax - toMin) * (value - fromMin)) / (fromMax - fromMin)
      );
    };

    const halfW = $wrap.clientWidth / 2;
    const halfH = $wrap.clientHeight / 2;

    let easer;
    $card.addEventListener("pointerenter", () => {
      if (easer) {
        easer?.cancel?.();
      }
      $card.classList.add("active");
      $wrap.classList.add("active");
    });
    $card.addEventListener("pointermove", cardUpdate);
    $card.addEventListener("pointerout", (e) => {
      easer = easedFunc(
        1000,
        (p) => {
          let x = adjust(p, 0, 1, e.offsetX, halfW);
          let y = adjust(p, 0, 1, e.offsetY, halfH);
          cardUpdate({ offsetX: x, offsetY: y });
        },
        () => {
          $card.classList.remove("active");
          $wrap.classList.remove("active");
        }
      );
    });

    // Initial position
    cardUpdate({ offsetX: $wrap.clientWidth - 70, offsetY: 60 });

    // Animate to center after a delay
    setTimeout(() => {
      easer = easedFunc(
        2000,
        (p) => {
          let x = adjust(p, 0, 1, $wrap.clientWidth - 70, halfW);
          let y = adjust(p, 0, 1, 60, halfH);
          cardUpdate({ offsetX: x, offsetY: y });
        },
        () => {
          $card.classList.remove("active");
          $wrap.classList.remove("active");
        }
      );
    }, 500);
  });
};

// Initialize on DOM ready
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", holoInit);
} else {
  holoInit();
}

// Re-initialize when new cards are added dynamically
if (typeof MutationObserver !== "undefined") {
  const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
      if (mutation.addedNodes.length) {
        mutation.addedNodes.forEach((node) => {
          if (
            node.nodeType === 1 &&
            (node.classList?.contains("rr-card-item") ||
              node.querySelector?.(".rr-card-item"))
          ) {
            holoInit();
          }
        });
      }
    });
  });

  const target = document.querySelector(".rr-cards-list");
  if (target) {
    observer.observe(target, { childList: true, subtree: true });
  }
}
