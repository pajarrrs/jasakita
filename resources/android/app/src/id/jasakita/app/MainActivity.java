package id.jasakita.app;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.ActivityNotFoundException;
import android.content.Intent;
import android.content.SharedPreferences;
import android.graphics.Color;
import android.net.Uri;
import android.os.Bundle;
import android.text.InputType;
import android.view.Gravity;
import android.view.View;
import android.view.ViewGroup;
import android.view.inputmethod.EditorInfo;
import android.webkit.CookieManager;
import android.webkit.WebChromeClient;
import android.webkit.WebResourceRequest;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.Button;
import android.widget.EditText;
import android.widget.FrameLayout;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;

/**
 * Aplikasi Android JasaKita: pembungkus WebView untuk aplikasi web.
 *
 * Alamat server bisa diganti langsung dari aplikasi ketika koneksi gagal,
 * sehingga APK yang sama tetap bisa dipakai di jaringan mana pun.
 */
public class MainActivity extends Activity {

    private static final String PREFS_NAME = "jasakita_app";
    private static final String PREF_SERVER_URL = "server_url";
    private static final int REQUEST_FILE_CHOOSER = 1001;

    private WebView webView;
    private ProgressBar progressBar;
    private LinearLayout errorPanel;
    private EditText serverUrlInput;
    private String serverUrl;
    private ValueCallbackHolder fileChooser;

    private static final class ValueCallbackHolder {
        android.webkit.ValueCallback<Uri[]> callback;

        void clear() {
            if (callback != null) {
                callback.onReceiveValue(null);
                callback = null;
            }
        }
    }

    @SuppressLint("SetJavaScriptEnabled")
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);

        SharedPreferences preferences = getSharedPreferences(PREFS_NAME, MODE_PRIVATE);
        serverUrl = normalizeServerUrl(
                preferences.getString(PREF_SERVER_URL, getString(R.string.default_server_url))
        );

        fileChooser = new ValueCallbackHolder();

        FrameLayout root = new FrameLayout(this);

        webView = new WebView(this);
        configureWebView(webView);
        root.addView(webView, matchParent());

        progressBar = new ProgressBar(this, null, android.R.attr.progressBarStyleHorizontal);
        progressBar.setMax(100);
        FrameLayout.LayoutParams progressParams = new FrameLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT, dp(3));
        progressParams.gravity = Gravity.TOP;
        root.addView(progressBar, progressParams);

        errorPanel = buildErrorPanel();
        root.addView(errorPanel, matchParent());
        errorPanel.setVisibility(View.GONE);

        setContentView(root);

        if (savedInstanceState == null) {
            webView.loadUrl(serverUrl + getString(R.string.start_path));
        } else {
            webView.restoreState(savedInstanceState);
        }
    }

    @SuppressLint("SetJavaScriptEnabled")
    private void configureWebView(WebView view) {
        WebSettings settings = view.getSettings();
        settings.setJavaScriptEnabled(true);
        settings.setDomStorageEnabled(true);
        settings.setDatabaseEnabled(true);
        settings.setLoadWithOverviewMode(true);
        settings.setUseWideViewPort(true);
        settings.setBuiltInZoomControls(false);
        settings.setDisplayZoomControls(false);
        settings.setMediaPlaybackRequiresUserGesture(false);
        settings.setMixedContentMode(WebSettings.MIXED_CONTENT_COMPATIBILITY_MODE);
        settings.setCacheMode(WebSettings.LOAD_DEFAULT);

        CookieManager cookieManager = CookieManager.getInstance();
        cookieManager.setAcceptCookie(true);
        cookieManager.setAcceptThirdPartyCookies(view, true);

        view.setBackgroundColor(Color.WHITE);
        view.setWebViewClient(new AppWebViewClient());
        view.setWebChromeClient(new AppChromeClient());
    }

    private FrameLayout.LayoutParams matchParent() {
        return new FrameLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT,
                ViewGroup.LayoutParams.MATCH_PARENT
        );
    }

    private int dp(int value) {
        return Math.round(value * getResources().getDisplayMetrics().density);
    }

    private LinearLayout buildErrorPanel() {
        LinearLayout panel = new LinearLayout(this);
        panel.setOrientation(LinearLayout.VERTICAL);
        panel.setGravity(Gravity.CENTER);
        panel.setBackgroundColor(Color.WHITE);
        panel.setPadding(dp(24), dp(24), dp(24), dp(24));

        TextView title = new TextView(this);
        title.setText(R.string.error_title);
        title.setTextSize(20);
        title.setGravity(Gravity.CENTER);
        title.setTextColor(Color.parseColor("#18181B"));
        panel.addView(title);

        TextView message = new TextView(this);
        message.setText(R.string.error_message);
        message.setTextSize(14);
        message.setGravity(Gravity.CENTER);
        message.setTextColor(Color.parseColor("#52525B"));
        LinearLayout.LayoutParams messageParams = new LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT);
        messageParams.topMargin = dp(12);
        panel.addView(message, messageParams);

        serverUrlInput = new EditText(this);
        serverUrlInput.setInputType(InputType.TYPE_TEXT_VARIATION_URI);
        serverUrlInput.setImeOptions(EditorInfo.IME_ACTION_GO);
        serverUrlInput.setHint(R.string.error_hint);
        serverUrlInput.setText(serverUrl);
        serverUrlInput.setSingleLine(true);
        LinearLayout.LayoutParams inputParams = new LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.WRAP_CONTENT);
        inputParams.topMargin = dp(20);
        panel.addView(serverUrlInput, inputParams);

        Button retry = new Button(this);
        retry.setText(R.string.error_retry);
        retry.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                openServer(serverUrlInput.getText().toString());
            }
        });
        LinearLayout.LayoutParams retryParams = new LinearLayout.LayoutParams(
                ViewGroup.LayoutParams.WRAP_CONTENT, ViewGroup.LayoutParams.WRAP_CONTENT);
        retryParams.topMargin = dp(16);
        retryParams.gravity = Gravity.CENTER_HORIZONTAL;
        panel.addView(retry, retryParams);

        serverUrlInput.setOnEditorActionListener(new TextView.OnEditorActionListener() {
            @Override
            public boolean onEditorAction(TextView view, int actionId, android.view.KeyEvent event) {
                if (actionId == EditorInfo.IME_ACTION_GO) {
                    openServer(serverUrlInput.getText().toString());
                    return true;
                }

                return false;
            }
        });

        return panel;
    }

    private void openServer(String input) {
        String candidate = normalizeServerUrl(input);

        if (candidate.isEmpty()) {
            Toast.makeText(this, R.string.error_invalid_url, Toast.LENGTH_SHORT).show();

            return;
        }

        serverUrl = candidate;
        getSharedPreferences(PREFS_NAME, MODE_PRIVATE)
                .edit()
                .putString(PREF_SERVER_URL, serverUrl)
                .apply();

        errorPanel.setVisibility(View.GONE);
        webView.loadUrl(serverUrl + getString(R.string.start_path));
    }

    private static String normalizeServerUrl(String value) {
        if (value == null) {
            return "";
        }

        String trimmed = value.trim();

        if (trimmed.isEmpty()) {
            return "";
        }

        if (!trimmed.startsWith("http://") && !trimmed.startsWith("https://")) {
            trimmed = "http://" + trimmed;
        }

        while (trimmed.endsWith("/")) {
            trimmed = trimmed.substring(0, trimmed.length() - 1);
        }

        return trimmed;
    }

    /**
     * Buka tautan luar (WhatsApp, telepon, email) di aplikasi lain,
     * biarkan tautan internal tetap dibuka di WebView.
     */
    private boolean handleUrl(String url) {
        if (url.startsWith("http://") || url.startsWith("https://")) {
            if (url.startsWith(serverUrl)) {
                return false;
            }

            try {
                startActivity(new Intent(Intent.ACTION_VIEW, Uri.parse(url)));
            } catch (ActivityNotFoundException ignored) {
                // Tidak ada aplikasi yang bisa membuka tautan ini.
            }

            return true;
        }

        try {
            startActivity(new Intent(Intent.ACTION_VIEW, Uri.parse(url)));
        } catch (ActivityNotFoundException ignored) {
            // Abaikan skema yang tidak dikenal.
        }

        return true;
    }

    private final class AppWebViewClient extends WebViewClient {
        @Override
        public boolean shouldOverrideUrlLoading(WebView view, WebResourceRequest request) {
            return handleUrl(request.getUrl().toString());
        }

        @Override
        public void onPageFinished(WebView view, String url) {
            progressBar.setVisibility(View.GONE);

            if (url.startsWith(serverUrl)) {
                errorPanel.setVisibility(View.GONE);
            }
        }

        @Override
        public void onReceivedError(WebView view, WebResourceRequest request, android.webkit.WebResourceError error) {
            if (request.isForMainFrame()) {
                progressBar.setVisibility(View.GONE);
                errorPanel.setVisibility(View.VISIBLE);
                serverUrlInput.setText(serverUrl);
            }
        }
    }

    private final class AppChromeClient extends WebChromeClient {
        @Override
        public void onProgressChanged(WebView view, int progress) {
            progressBar.setVisibility(progress < 100 ? View.VISIBLE : View.GONE);
            progressBar.setProgress(progress);
        }

        @Override
        public boolean onShowFileChooser(
                WebView view,
                android.webkit.ValueCallback<Uri[]> callback,
                FileChooserParams params
        ) {
            fileChooser.clear();
            fileChooser.callback = callback;

            try {
                startActivityForResult(params.createIntent(), REQUEST_FILE_CHOOSER);

                return true;
            } catch (ActivityNotFoundException ignored) {
                fileChooser.clear();

                return false;
            }
        }
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        if (requestCode == REQUEST_FILE_CHOOSER) {
            if (fileChooser.callback != null) {
                fileChooser.callback.onReceiveValue(
                        WebChromeClient.FileChooserParams.parseResult(resultCode, data)
                );
                fileChooser.callback = null;
            }

            return;
        }

        super.onActivityResult(requestCode, resultCode, data);
    }

    @Override
    protected void onSaveInstanceState(Bundle outState) {
        super.onSaveInstanceState(outState);

        webView.saveState(outState);
    }

    @Override
    public void onBackPressed() {
        if (errorPanel.getVisibility() == View.VISIBLE) {
            errorPanel.setVisibility(View.GONE);
            webView.setVisibility(View.VISIBLE);

            return;
        }

        if (webView.canGoBack()) {
            webView.goBack();

            return;
        }

        super.onBackPressed();
    }

    @Override
    protected void onPause() {
        super.onPause();

        CookieManager.getInstance().flush();
    }
}
