const characteristics = [
  {
    name: "Problem Solving",
    explanation: "Solves a real pain point or daily struggle",
    why: "People buy solutions, not just products",
    example: "Posture corrector for back pain",
  },
  {
    name: "Wow / Novelty Factor",
    explanation: "Unique, eye-catching, or entertaining",
    why: "Triggers curiosity + impulse purchases",
    example: "Magnetic phone case that sticks to any surface",
  },
  {
    name: "Viral Potential",
    explanation: "Easy to demonstrate in short video (TikTok, Reels)",
    why: "Products with 'show effect' go viral",
    example: "Mini portable blender",
  },
  {
    name: "Affordable Price",
    explanation: "Selling price between $15 – $50",
    why: "Low enough for impulse, high enough for profit",
    example: "$5 gadget → sold for $24.99",
  },
  {
    name: "High Perceived Value",
    explanation: "Looks premium compared to cost",
    why: "Justifies higher markup",
    example: "LED mirror with touch sensor",
  },
  {
    name: "Good Profit Margin",
    explanation: "≥ 60% gross margin (2.5x-3x markup)",
    why: "Gives room for ads + profit",
    example: "$6 cost → sold at $24.99",
  },
  {
    name: "Broad Audience",
    explanation: "Many potential buyers (not too niche)",
    why: "Easier scaling with ads",
    example: "Fitness resistance bands",
  },
  {
    name: "Seasonal / Evergreen Fit",
    explanation: "Works year-round or during trending season",
    why: "Ensures consistent demand",
    example: "Summer cooling fan (seasonal) / pet grooming kit (evergreen)",
  },
  {
    name: "Lightweight & Small",
    explanation: "Easy to ship, low cost, less risk",
    why: "Faster delivery + fewer returns",
    example: "Kitchen gadget under 500g",
  },
  {
    name: "Low Return/Complaint Risk",
    explanation: "Simple, not fragile, not 'too good to be true'",
    why: "Reduces refund problems",
    example: "Phone stand vs. skincare with side effects",
  },
  {
    name: "No Legal/Trademark Issues",
    explanation: "Not branded, no IP violation",
    why: "Prevents account bans",
    example: "Generic jewelry vs. Disney logo watch",
  },
  {
    name: "Bundling Potential",
    explanation: "Can sell 2–3 units or related add-ons",
    why: "Increases Average Order Value (AOV)",
    example: "3-pack toothbrush heads",
  },
  {
    name: "Scalable Market Demand",
    explanation: "Trending on TikTok/Amazon + stable Google Trends",
    why: "Confirms there's an audience",
    example: "LED strip lights",
  },
  {
    name: "Good Supplier Support",
    explanation: "Reliable shipping, high reviews",
    why: "Avoids disputes + angry customers",
    example: "4.7★ AliExpress supplier",
  },
];

const characteristicsContainer = document.getElementById("characteristics");
const scoreInputsContainer = document.getElementById("score-inputs");

characteristics.forEach((char, index) => {
  const charElement = document.createElement("div");
  charElement.className = "characteristic";
  charElement.innerHTML = `
                <h3>${char.name}</h3>
                <p><strong>Explanation:</strong> ${char.explanation}</p>
                <p><strong>Why it matters:</strong> ${char.why}</p>
                <p><strong>Example:</strong> ${char.example}</p>
                <label>Score (0-10): <span class="score-value" id="score-${index}">5</span></label>
                <input type="range" class="score-slider" id="slider-${index}" min="0" max="10" value="5" 
                    data-characteristic="${char.name}">
                <textarea placeholder="Add notes (optional)" rows="2" data-characteristic="${char.name}" class="notes-input" name="notes[${char.name}]"></textarea>
            `;
  characteristicsContainer.appendChild(charElement);

  const scoreInput = document.createElement("input");
  scoreInput.type = "hidden";
  scoreInput.name = `scores[${char.name}][score]`;
  scoreInput.id = `input-score-${index}`;
  scoreInput.value = "5";

  const notesInput = document.createElement("input");
  notesInput.type = "hidden";
  notesInput.name = `scores[${char.name}][notes]`;
  notesInput.id = `input-notes-${index}`;
  notesInput.value = "";

  scoreInputsContainer.appendChild(scoreInput);
  scoreInputsContainer.appendChild(notesInput);

  const slider = document.getElementById(`slider-${index}`);
  const scoreDisplay = document.getElementById(`score-${index}`);
  const notesField = charElement.querySelector(".notes-input");

  slider.addEventListener("input", function () {
    scoreDisplay.textContent = this.value;
    document.getElementById(`input-score-${index}`).value = this.value;
    updateTotalScore();
  });

  notesField.addEventListener("input", function () {
    document.getElementById(`input-notes-${index}`).value = this.value;
  });
});

function updateTotalScore() {
  let total = 0;
  const sliders = document.querySelectorAll(".score-slider");

  sliders.forEach((slider) => {
    total += parseInt(slider.value);
  });

  document.getElementById("total-score").textContent = total;
  document.getElementById("save-total-score").value = total;

  const percentage = (total / 140) * 100;
  document.getElementById("score-visual").style.width = `${percentage}%`;

  const feedback = document.getElementById("score-feedback");
  if (total >= 120) {
    feedback.textContent =
      "Excellent! This product has strong winning potential.";
    feedback.style.color = "green";
  } else if (total >= 90) {
    feedback.textContent = "Good product with many winning characteristics.";
    feedback.style.color = "orange";
  } else if (total >= 60) {
    feedback.textContent =
      "Average product. Consider improvements in weak areas.";
    feedback.style.color = "#e0a800";
  } else {
    feedback.textContent =
      "Needs significant improvement to be a winning product.";
    feedback.style.color = "red";
  }
}

document.querySelectorAll(".tab").forEach((tab) => {
  tab.addEventListener("click", function () {
    document
      .querySelectorAll(".tab")
      .forEach((t) => t.classList.remove("active"));
    document
      .querySelectorAll(".tab-content")
      .forEach((c) => c.classList.remove("active"));

    this.classList.add("active");
    document.getElementById(`${this.dataset.tab}-tab`).classList.add("active");

    const url = new URL(window.location);
    url.searchParams.set("tab", this.dataset.tab);

    if (this.dataset.tab !== "saved") {
      url.searchParams.delete("view_product");
    }

    window.history.replaceState({}, "", url);
  });
});

updateTotalScore();

document.querySelectorAll(".sub-tab").forEach((tab) => {
  tab.addEventListener("click", function () {
    document
      .querySelectorAll(".sub-tab")
      .forEach((t) => t.classList.remove("active"));
    document
      .querySelectorAll(".sub-tab-content")
      .forEach((c) => c.classList.remove("active"));
    this.classList.add("active");
    document
      .getElementById(`subtab-${this.dataset.subtab}`)
      .classList.add("active");
  });
});

function toggleForm(id) {
  const el = document.getElementById(id);
  el.style.display = el.style.display === "none" ? "block" : "none";
}
