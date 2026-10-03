<?php $title = 'Coffee for your morning'; include 'includes/header.php'; ?>
<main id="main">
<section class="hero"><div class="wrap hero-grid">
  <div>
    <h1>Coffee for <em>your morning.</em></h1>
    <p class="lead">Espresso, iced drinks and house favourites, made to order. Not sure what to have? Tell us the mood.</p>
    <form class="mood" action="menu.php" method="get" role="search">
      <label for="mood" class="skip" style="position:absolute">What are you in the mood for?</label>
      <input id="mood" name="q" placeholder="Try “iced”, “strong” or “chocolate”">
      <button class="btn gold" type="submit">Find my drink</button>
    </form>
    <div class="chips" aria-label="Quick picks">
      <a class="chip" href="menu.php?q=iced">Iced</a><a class="chip" href="menu.php?q=strong">Strong</a>
      <a class="chip" href="menu.php?q=chocolate">Chocolate</a><a class="chip" href="menu.php?q=creamy">Creamy</a>
    </div>
  </div>
  <div class="hero-img"><img src="assets/images/coffee.jpg" alt="Latte with leaf art in a dark ceramic cup"><div class="badgebar"><span>Made to order</span><b>House menu</b></div></div>
</div></section>

<section class="section"><div class="wrap">
  <div class="section-heading"><div><h2>Popular drinks</h2><p>Espresso, milk drinks and cold coffee.</p></div><a class="link-arrow" href="menu.php">See the full menu</a></div>
  <div class="feature-grid">
    <a class="feature-photo" href="menu.php"><img src="assets/images/coffee3.jpg" alt="House latte on a stone counter"><div><h3>House Latte</h3><p>Smooth espresso, steamed milk, soft foam.</p></div></a>
    <div class="drink-list">
      <a href="menu.php?q=cappuccino"><div><h3>Cappuccino</h3><p>Espresso, steamed milk, velvety foam</p></div><b>$5.50</b></a>
      <a href="menu.php?q=iced+latte"><div><h3>Iced Latte</h3><p>Espresso over cold milk and ice</p></div><b>$6.00</b></a>
      <a href="menu.php?q=long+black"><div><h3>Long Black</h3><p>Double espresso finished with hot water</p></div><b>$4.50</b></a>
    </div>
  </div>
</div></section>

<section id="approach" class="section approach"><div class="wrap two-col">
  <h2>Good coffee, kept simple.</h2>
  <div><p>A short menu of café favourites, prepared fresh. No complicated process: pick a drink, place your order, and we’ll have it ready.</p><a href="menu.php">Explore the menu</a></div>
</div></section>

<section id="visit" class="section visit"><div class="wrap visit-card">
  <div><h2>Your next coffee is waiting.</h2><p>Browse the menu online and order when you’re ready.</p></div>
  <a class="btn dark" href="menu.php">Order coffee</a>
</div></section>
</main>
<?php include 'includes/footer.php'; ?>
