# Cloudflare DNS 管理面板

- 此面板使用了 `Cloudflare API v4`，支持管理各种格式的 `DNS` 记录

- 支持管理原 `CNAME` 接入的域名，**不支持添加新域名**

- 支持 `NS` 接入。此面板提供了 `NS` 接入信息，所以你可以随时切换到 `Cloudflare DNS`

- 支持 `IP` 接入。提供 `DNS` 的 `Anycast IPv4` 和 `IPv6` 地址，这样你可以安全地在根域名下使用第三方 `DNS`

- 支持 `DNSSEC` 设置。建议仅在 `NS` 模式下启用，否则可能无法导致解析

- 支持多种语言。当前仅支持 `中文` 和 `英文`

- 适配移动设备

## 其他说明

本项目原始版本基于 https://github.com/ZE3kr/Cloudflare-CNAME-Setup 二次开发， 感谢 ZE3kr 的无私贡献。
