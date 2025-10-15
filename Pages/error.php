<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>404 - Page Not Found</title>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      background-color: #f9fafb;
      display: flex;
      justify-content: center;
      align-items: center;
      /* Fixed height to prevent scrolling */
      height: 100vh;
      overflow: hidden;
      padding: 1rem;
      position: relative;
    }

    /* Added decorative shapes */
    .shape {
      position: absolute;
      opacity: 0.1;
      pointer-events: none;
    }

    .circle-1 {
      width: 300px;
      height: 300px;
      border-radius: 50%;
      background-color: #0066ff;
      top: -100px;
      left: -100px;
    }

    .circle-2 {
      width: 200px;
      height: 200px;
      border-radius: 50%;
      background-color: #0066ff;
      bottom: -50px;
      right: -50px;
    }

    .square-1 {
      width: 150px;
      height: 150px;
      background-color: #374151;
      transform: rotate(45deg);
      top: 10%;
      right: 5%;
    }

    .square-2 {
      width: 100px;
      height: 100px;
      background-color: #374151;
      transform: rotate(25deg);
      bottom: 15%;
      left: 8%;
    }

    .circle-3 {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      border: 20px solid #0066ff;
      background-color: transparent;
      top: 60%;
      right: 10%;
    }

    .circle-4 {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background-color: #6b7280;
      top: 20%;
      left: 15%;
    }

    .triangle {
      width: 0;
      height: 0;
      border-left: 75px solid transparent;
      border-right: 75px solid transparent;
      border-bottom: 130px solid #0066ff;
      bottom: 20%;
      right: 20%;
    }

    .container {
      padding: 2rem;
      max-width: 32rem;
      width: 100%;
      text-align: center;
      /* Added z-index to keep content above shapes */
      position: relative;
      z-index: 1;
      margin-left: 100px;
    }

    .error-code {
      font-size: 6rem;
      font-weight: 700;
      color: #1f2937;
      line-height: 1;
      margin-bottom: 1rem;
    }

    .error-message {
      font-size: 1.25rem;
      color: #374151;
      margin-bottom: 2rem;
      line-height: 1.6;
    }

    .description {
      font-size: 1rem;
      color: #6b7280;
      margin-bottom: 2rem;
      line-height: 1.6;
    }

    .suggestions {
      list-style: none;
      margin-bottom: 2rem;
      padding: 0;
    }

    .suggestions li {
      font-size: 0.9375rem;
      color: #6b7280;
      margin-bottom: 0.75rem;
      line-height: 1.5;
    }

    .suggestions li::before {
      content: "•";
      color: #0066ff;
      font-weight: bold;
      display: inline-block;
      width: 1.5rem;
    }

    .button-group {
      display: flex;
      gap: 1rem;
      justify-content: center;
      flex-wrap: wrap;
    }

    .home-button {
      display: inline-block;
      padding: 0.875rem 1.5rem;
      background-color: #0066ff;
      color: white;
      text-decoration: none;
      border-radius: 0.375rem;
      font-size: 1rem;
      font-weight: 500;
      transition: background-color 0.2s;
      border: none;
      cursor: pointer;
      min-width: 140px;
    }

    .home-button:hover {
      background-color: #0052cc;
    }

    .home-button:active {
      background-color: #0047b3;
    }

    .secondary-button {
      display: inline-block;
      padding: 0.875rem 1.5rem;
      background-color: white;
      color: #374151;
      text-decoration: none;
      border-radius: 0.375rem;
      font-size: 1rem;
      font-weight: 500;
      transition: background-color 0.2s;
      border: 1px solid #d1d5db;
      cursor: pointer;
      min-width: 140px;
    }

    .secondary-button:hover {
      background-color: #f9fafb;
    }

    .help-text {
      margin-top: 2rem;
      font-size: 0.875rem;
      color: #9ca3af;
    }

    .help-text a {
      color: #0066ff;
      text-decoration: none;
    }

    .help-text a:hover {
      text-decoration: underline;
    }

    @media (max-width: 640px) {
      .error-code {
        font-size: 4rem;
      }

      .error-message {
        font-size: 1.125rem;
      }

      .container {
        padding: 1.5rem 1rem;
      }

      .button-group {
        flex-direction: column;
      }

      .home-button,
      .secondary-button {
        width: 100%;
      }

      /* Adjust shape sizes for mobile */
      .circle-1 {
        width: 200px;
        height: 200px;
      }

      .circle-2 {
        width: 150px;
        height: 150px;
      }

      .square-1,
      .square-2 {
        width: 80px;
        height: 80px;
      }

      .circle-3 {
        width: 80px;
        height: 80px;
        border-width: 15px;
      }

      .triangle {
        border-left: 50px solid transparent;
        border-right: 50px solid transparent;
        border-bottom: 85px solid #0066ff;
      }
    }
  </style>
</head>
<body>
  <div class="shape circle-1"></div>
  <div class="shape circle-2"></div>
  <div class="shape square-1"></div>
  <div class="shape square-2"></div>
  <div class="shape circle-3"></div>
  <div class="shape circle-4"></div>
  <div class="shape triangle"></div>

  <div class="container">
    <div class="error-code">404</div>
    <p class="error-message">Something went wrong. Please try again.</p>
    
    <p class="description">
      The page you're looking for doesn't exist or has been moved.
    </p>

    <!-- <ul class="suggestions">
      <li>Check the URL for typos</li>
      <li>Return to the homepage</li>
      <li>Use the search feature</li>
      <li>Contact support if the problem persists</li>
    </ul> -->

    <div class="button-group">
      <a href="/" class="home-button">Go Back Home</a>
      <a href="javascript:history.back()" class="secondary-button">Go Back</a>
    </div>

    <p class="help-text">
      Need help? <a href="/contact">Contact Support</a>
    </p>
  </div>
</body>
</html>
