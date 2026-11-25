"use client";

import Image from "next/image";
import Link from "next/link";
import { useState } from "react";
import styles from "@/app/home.module.css";

const works = [
  {
    file: "wedding",
    title: "Wedding Film",
    description: "Dokumentasi pernikahan",
    label: "Wedding",
    alt: "Pasangan pengantin berdansa di bawah rangkaian lampu hangat",
  },
  {
    file: "commercial",
    title: "Commercial Film",
    description: "Kampanye produk",
    label: "Commercial",
    alt: "Sepatu warna-warni melayang dengan latar kuning",
  },
  {
    file: "coast",
    title: "Documentary",
    description: "Sinematografi lanskap",
    label: "Documentary",
    alt: "Garis pantai dengan ombak dan perbukitan saat matahari terbenam",
  },
  {
    file: "music-video",
    title: "Music Video",
    description: "Film musik dan color grading",
    label: "Music Video",
    alt: "Performer di antara sorotan lampu merah dan biru",
  },
] as const;

const categories = ["Semua", ...works.map((work) => work.label)] as const;

export function HomeWorks() {
  const [category, setCategory] = useState<(typeof categories)[number]>("Semua");
  const filtered = category !== "Semua";
  const visibleWorks = filtered ? works.filter((work) => work.label === category) : works;

  return (
    <section id="works" className={styles.works} aria-labelledby="works-title">
      <div className={styles.worksHeader}>
        <div>
          <h2 id="works-title" lang="en">Selected <span>Works.</span></h2>
          <p>Studi visual untuk foto, film, dan pascaproduksi.</p>
        </div>
        <Link className={styles.textLink} href="/works">
          Lihat portfolio <span aria-hidden="true">↗</span>
        </Link>
      </div>

      <div className={styles.filters} role="group" aria-label="Filter karya">
        {categories.map((label) => (
          <button
            key={label}
            type="button"
            aria-pressed={label === category}
            aria-controls="works-grid"
            onClick={() => setCategory(label)}
          >
            {label}
          </button>
        ))}
      </div>

      <div id="works-grid" className={styles.worksGrid}>
        {visibleWorks.map(({ file, title, description, alt }) => (
          <article key={file} className={styles.work}>
            <div className={styles.workImage}>
              <Image
                src={`/images/home/${file}.webp`}
                alt={alt}
                fill
                sizes="(width < 810px) calc(100vw - 50px), (width < 1200px) calc(100vw - 100px), calc((100vw - 215px) / 2)"
              />
            </div>
            <div className={styles.workCaption}>
              <h3 lang="en">{title}</h3>
              <p>{description}</p>
            </div>
          </article>
        ))}
      </div>
    </section>
  );
}
