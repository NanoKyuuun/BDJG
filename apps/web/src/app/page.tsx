import type { Metadata } from "next";
import { Roboto_Condensed, IBM_Plex_Mono } from "next/font/google";
import Link from "next/link";
import { BookingWizard } from "@/components/booking-wizard";
import { PublicFooter } from "@/components/public-footer";
import { PublicNav } from "@/components/public-nav";
import { HomeHero } from "@/components/home-hero";
import { HomeServices } from "@/components/home-services";
import { HomeWorks } from "@/components/home-works";
import styles from "./home.module.css";

export const metadata: Metadata = {
  title: "BDJG Studio — Photography, Film & Post Production",
  description: "BDJG adalah studio kreatif untuk fotografi, film, dan pascaproduksi. Lihat karya dan ceritakan proyekmu.",
};

const display = Roboto_Condensed({ subsets: ["latin"], weight: ["500", "600"], variable: "--font-public-display", display: "swap" });
const mono = IBM_Plex_Mono({ subsets: ["latin"], weight: ["400", "500"], variable: "--font-public-mono", display: "swap" });

export default function Home() {
  return (
    <div className={`${styles.home} ${display.variable} ${mono.variable}`}>
      <a className={styles.skip} href="#main-content">Lewati ke konten</a>
      <PublicNav />

      <main id="main-content" lang="id" tabIndex={-1}>
        <HomeHero />

        <HomeWorks />

        <section id="studio" className={styles.studio} aria-labelledby="studio-title">
          <div className={styles.studioIntro}>
            <p className={styles.eyebrow}>Tentang BDJG</p>
            <h2 id="studio-title">Cerita dalam <span>setiap frame.</span></h2>
            <div>
              <p>Dari treatment pertama sampai final cut, kami menangani foto, film, dan pascaproduksi sebagai satu proses kreatif.</p>
              <Link className={styles.textLink} href="/studio">
                Kenali studio <span aria-hidden="true">↗</span>
              </Link>
            </div>
          </div>
        </section>

        <HomeServices />

        <section id="book" className={styles.booking} aria-labelledby="book-title">
          <div className={styles.bookingIntro}>
            <h2 id="book-title">Book a <span>project.</span></h2>
            <p>Ceritakan proyek yang ingin kamu buat. Mulai dengan layanan, tanggal, dan brief singkat.</p>
          </div>
          <div lang="en"><BookingWizard /></div>
        </section>
      </main>

      <PublicFooter />
    </div>
  );
}
