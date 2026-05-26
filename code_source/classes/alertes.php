<?php

    require_once __DIR__ . '/../config.php';

    class Alertes
    {
        private function generateAlert(string $message, string $type): string
        {
            return sprintf('<div class="alert alert-%s text-center mx-auto" style="max-width: 500px;" role="alert">%s</div>', $type, $message);
        }

        public function alert_primary(string $message): string
        {
            return $this->generateAlert($message, 'primary');
        }

        public function alert_secondary(string $message): string
        {
            return $this->generateAlert($message, 'secondary');
        }

        public function alert_success(string $message): string
        {
            return $this->generateAlert($message, 'success');
        }

        public function alert_danger(string $message): string
        {
            return $this->generateAlert($message, 'danger');
        }

        public function alert_warning(string $message): string
        {
            return $this->generateAlert($message, 'warning');
        }

        public function alert_info(string $message): string
        {
            return $this->generateAlert($message, 'info');
        }

        public function alert_light(string $message): string
        {
            return $this->generateAlert($message, 'light');
        }
    }
?>